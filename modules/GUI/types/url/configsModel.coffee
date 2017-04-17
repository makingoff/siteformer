Model = require "libs/model.coffee"
configs = require "types/url/configs.json"

module.exports = class UrlConfigsModel extends Model
  constructor: (state = {}) ->
    super state

  initial: ->
    source = ""

    @state.fields.forEach (item) ->
      if !source && item.alias
        source = item.alias

    @set settings: {source} if !@state.settings.source

  defaultState: ->
    settings: configs.defaultSettings
    source: ""

  getState: ->
    settings: @state.settings
    index: @state.index

  updateField: (field) -> @set settings: source: field
