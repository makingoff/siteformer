View = require "view.coffee"
Render = require "render"
modalWindowTemplate = require "types/section/modal"

module.exports = View
  events:
    "submit: [data-role='configs-form']": "submitConfigsForm"
    "change: [data-role='configs-section-section']": (e) -> @model.updateSection e.target.value
    "change: [data-role='configs-section-field']": (e) -> @model.updateField e.target.value
    "popup-close: contain": (e) -> @destroy()

  initial: -> @modalContain = Render modalWindowTemplate, @contain[0]

  render: (state) -> @modalContain state

  submitConfigsForm: (e) ->
    @trigger "save-configs-modal", @model.getState()
    return false
