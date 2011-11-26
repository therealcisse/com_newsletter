Spine = require('spine')
Subscriber = require('models/subscriber')
$ = Spine.$

MultiList = require('lib/multilist')

Subscriber = require('models/subscriber')
Subscribers = require('controllers/subscribers')

class Toolsbar extends Spine.Controller

  @include Spine.Log

  logPrefix: '(Toolsbar)'

  className: 'toolsbar'

  events:
    'change #bulk-actions'  : 'bulk'
    'change #sort-actions'  : 'sort'
    'click  #refresh'       : 'reload'

  elements:
    '#bulk-actions'   : 'bulkActions'
    '#sort-actions'   : 'sortActions'

  constructor: ->
    super

    @render()

  reload: ->
    @trigger('reload')

  tmpl: require('views/toolsbar')

  render: ->
    @replace $ @tmpl()

  # ------------- BULK ACTIONS ------------------------------- # Bulk delete, publish, unpublish in a dropdown

  bulkAction: () ->
    @bulkActions.val()

  bulk: (e) ->
    action = @bulkAction()
    @trigger('bulk', action) if action in ['delete', 'publish', 'unpublish', 'activate', 'deactivate']

    @delay (=> @bulkActions.val('direction')), 100

    # reset the selected item to first

  # ------------- SORTING ------------------------------- # sort by : most recent, name, email, category count, language count

  getSortKey: ->
    @sortActions.val()

  dir: {} # 1 = asc, -1 = desc,

  sort: (e) ->
    key = @getSortKey()
    @dir[key] = -1 * (@dir[key] ||= 1)
    @trigger('sort', key, @dir[key]) if key in ['recent', 'name', 'email', 'category', 'language']

class Filters extends Spine.Controller

  className: 'filtering'

  events:
    'keyup #search'            : 'search'
    'change #filter-category'  : 'search'
    'change #filter-language'  : 'search'
    'submit'                   : 'search'

  elements:
    '#search'             : 'searchFilter'
    '#filter-category'    : 'categoryFilter'
    '#filter-language'    : 'languageFilter'

  constructor: ->
    super

    Subscriber.bind 'categories-refresh languages-refresh', => @render() if @isActive()

    @render()

  tmpl: require('views/filters')

  render: ->
    @replace $ @tmpl(@)

  search: ->
    clearTimeout(@timeout) if @timeout

    @timeout = @delay (=>
        [s, cat, lang] = [@getSearch(), @getCategory(), @getLanguage()]
        @trigger('search', s, cat, lang) if s? or cat? or lang?
      ), 100


#    clearTimeout(@timeout) if @timeout
#    @timeout =
#      @delay (=> @trigger('search', s, cat, lang)), 1000  if s? and cat? and lang?

    false

  getCategories: -> Subscriber.getCategories()
  getCategory: ->
    val = @categoryFilter.val()
    return val unless val is 'direction'
    null

  getLanguages: -> Subscriber.getLanguages()
  getLanguage: ->
    val = @languageFilter.val()
    return val unless val is 'direction'
    null

  getSearch: ->
    @searchFilter.val()?.trim()

  matches: (item) =>
    [s, c, lang] = [@getSearch(), @getCategory(), @getLanguage()]
    item.matches(s, c, lang)

  filtered: (records) =>
    [s, c, lang] = [@getSearch(), @getCategory(), @getLanguage()]
    record for record in records when record.matches(s, c, lang)

## Holds toolsbar, filters and MultiList
class Index extends Spine.Controller

  className: 'index controller'

  constructor: ->
    super

    @toolsbar = new Toolsbar
    @filters  = new Filters(isActive: => @isActive())
    @subscriberList = new MultiList(model: Subscribers)

    @append @toolsbar, @filters, @subscriberList

module.exports = Index