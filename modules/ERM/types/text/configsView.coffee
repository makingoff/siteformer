View = require "libs/view.coffee"
Render = require "libs/render"
modalWindowTemplate = require "dist/types/text/modal.tmplt"

module.exports = class TextConfigsView extends View
  constructor: (target, model) ->
    super target, model

    @modalContain = Render modalWindowTemplate, @contain
    @render @model.state

  events:
    "submit: [data-role='configs-form']": "submitConfigsForm"
    "change: [data-role='configs-text-default-text']": (e) -> @model.updateDefaultText e.target.value
    "popup-close: contain": -> @destroy()

  render: (state) -> @modalContain state

  submitConfigsForm: (e) ->
    @trigger "save-configs-modal", @model.getState()
    e.preventDefault()