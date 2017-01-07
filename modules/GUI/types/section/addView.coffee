View = require "view.coffee"
Render = require "render"
itemTemplate = require "types/section/item"

module.exports = View
  initial: -> @templateRender = Render itemTemplate, @contain[0]

  events:
    "change input keyup: [data-role='section']": (e) ->
      if e.keyCode == 27 then @model.emptySearch() else @model.search e.target.value
    "click: [data-role='suggest-item']": (e) ->
      @model.selectResult Number(e.target.getAttribute "data-id"), e.target.getAttribute "data-title"
    "click: [data-role='cancel']": ->
      @model.emptyValue()
      setTimeout =>
        @contain.find("[data-role='section']").focus()
      , 50

  render: (state) -> @templateRender state
