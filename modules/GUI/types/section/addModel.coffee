Model = require "libs/model.coffee"
{httpGet, skipEmptyQuery, skipLastQuery, throttle} = require "libs/helpers.coffee"

fetchData = (url, section, field) -> (query) -> httpGet url,
  src: query
  section: section
  field: field

setResults = (data) ->
  @set searchResult: data.result

module.exports = class SectionDataModel extends Model
  constructor: (state = {}) ->
    super state

    @set
      searchSection: @state.settings.section
      searchField: @state.settings.field

  selectResult: (id, title) ->
    data =
      id: id
      title: title

    @set {data}
    @emptySearch()

  emptySearch: -> @set searchResult: []

  emptyValue: ->
    data =
      id: ""
      title: ""

    @set {data}
    @emptySearch()

  search: (value) ->
    Promise.resolve value
      .then skipLastQuery "#{@state.alias}"
      .then skipEmptyQuery
      .then throttle 500
      .then fetchData "/cms/types/section/search/", @state.searchSection, @state.searchField
      .then setResults.bind @
      .catch @emptySearch.bind @

  get: -> @state.data.id
