View = require "libs/view.coffee"

module.exports = class SelectDataView extends View
  constructor: (target, model) -> super target, model

  events:
    "change: [data-role='select']": (e) -> @model.update e.target.value