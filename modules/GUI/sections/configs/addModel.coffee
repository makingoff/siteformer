Model = require "libs/model.coffee"
{httpGet, httpPost} = require "libs/ajax.coffee"
{cloneObject} = require "libs/helpers.coffee"

module.exports = class AddConfigsModel extends Model
  constructor: (state = {}) ->
    defaultState =
      title: ""
      alias: ""
      module: ""
      modules: []
      fields: []
      types: []
      sections: []
    super Object.assign {}, defaultState, state

    httpGet window.location.pathname
      .then (response) =>
        state = cloneObject response
        console.log state
        @replace state

  addField: (field) ->
    @set fields: @state.fields.concat [field]

  addEmptyField: ->
    field = [
      title: ""
      alias: ""
      type: "string"
      position: @state.fields.length
      section: @state.id
      required: 0
    ]

    for typeItem in @state.types
      if typeItem.type == "string"
        field[0].settings = cloneObject typeItem.defaultSettings

    @set fields: @state.fields.concat field

  updateTitle: (value) ->
    @set
      title: value
      "error-title": ""

  updateAlias: (value) ->
    @set
      alias: value
      "error-alias": ""

  updateModule: (value) -> @set module: value

  updateFieldTitle: (index, value) ->
    fields = @state.fields.slice()
    fields[index].title = value
    @set
      fields: fields
      "error-fields-index": false
      "error-fields-field": ""
      "error-fields-message": ""

  updateFieldAlias: (index, value) ->
    fields = @state.fields.slice()
    fields[index].alias = value
    @set
      fields: fields
      "error-fields-index": false
      "error-fields-field": ""
      "error-fields-message": ""

  updateFieldType: (index, value) ->
    fields = @state.fields.slice()
    fields[index].type = value
    @resetSettings index
    @set {fields}

  updateFieldRequired: (index, value) ->
    fields = @state.fields.slice()
    fields[index].required = value ? 1 : 0
    @set {fields}

  resetSettings: (index) ->
    fields = @state.fields.slice()
    type = fields[index].type
    for typeItem in @state.types
      if typeItem.type == type
        fields[index].settings = cloneObject typeItem.defaultSettings
    @set {fields}

  removeField: (index) ->
    fields = @state.fields.slice()
    fields.splice index, 1
    fields.forEach (field, index) -> field.position = index
    @set {fields}

  getFieldByIndex: (index) -> cloneObject @state.fields[index]

  getFields: -> @state.fields.slice()

  saveFieldSettings: (state) ->
    fields = @state.fields.slice()
    fields[state.index].settings = cloneObject state.settings
    if @state["error-fields-field"]? && @state["error-fields-field"] == "settings"
      @set
        fields: fields
      "error-fields-index": false
      "error-fields-field": ""
      "error-fields-message": ""
    else
      @set {fields}

  updatePosition: (currentIndex, newIndex) ->
    fields = @getFields()

    different = newIndex - currentIndex

    if different
      if different > 0
        fields.forEach (field) ->
          if currentIndex < field.position <= newIndex
            field.position -= 1
            return

          if field.position == currentIndex
            field.position = newIndex
            return

      if different < 0
        fields.forEach (field) ->
          if currentIndex > field.position >= newIndex
            field.position += 1
            return

          if field.position == currentIndex
            field.position = newIndex
            return

      fields.sort (a, b) -> a.position - b.position

      @set {fields}

  save: ->
    data =
      alias: @state.alias
      title: @state.title
      module: @state.module
      fields: @state.fields

    data.id = @state.id if @state.id?

    console.log data

    httpPost "/cms/configs/action_save/", data
      .then (response) =>
        console.log response.content if response.content?
        if @state.id?
          # @set fields: response.section.fields
          @set id: response.section.id
        else
          @trigger "save-section", @state.alias
      .catch (response) =>
        @showError response.error.message if response.error? and response.error.message?

  showError: (error) ->
    switch error.index[0]
      when "title" then @set "error-title": @getTitleErrorMessage error.code
      when "alias" then @set "error-alias": @getAliasErrorMessage error.code
      when "fields" then @showErrorFields error

  getTitleErrorMessage: (code, field = "Раздел ") ->
    switch code
      when "EEMPTYREQUIRED" then "Поле обязательно к заполнению"
      when "ENOTUNIQUEVALUE" then "#{field}с таким именем уже есть, придумайте другое"
      else "Неизвестная ошибка: #{code}"

  getAliasErrorMessage: (code, field = "Раздел ") ->
    switch code
      when "EEMPTYREQUIRED" then "Поле обязательно к заполнению"
      when "ENOTUNIQUEVALUE" then "#{field}с таким веб-именем уже есть, придумайте другое"
      when "ENOTVALIDVALUE" then "Веб-имя может состоять только из символов латинского алфавита, дефис и подчеркивания"
      else "Неизвестная ошибка: #{code}"

  showErrorFields: (error) ->
    @set
      "error-fields-index": error.index[1]
      "error-fields-field": error.index[2]
      "error-fields-message": @getFieldsErrorMessage error.index[2], error.code

    if error.index[2] == "settings"
      settingsErrorIndex = error.index.slice(0)
      settingsErrorIndex.splice 0, 3
      settingsErrorCode = error.code
      fields = @state.fields.slice(0)
      fields[error.index[1]].settings.errorIndex = settingsErrorIndex
      fields[error.index[1]].settings.errorCode = settingsErrorCode

  getFieldsErrorMessage: (field, code) ->
    switch field
      when "title" then @getTitleErrorMessage code, "Поле "
      when "alias" then @getAliasErrorMessage code, "Поле "
      when "settings" then "Задайте настройки"

  getSections: -> @state.sections

  remove: ->
    httpPost "/cms/configs/action_delete/", id: @state.id
      .then => @trigger "delete-section"
