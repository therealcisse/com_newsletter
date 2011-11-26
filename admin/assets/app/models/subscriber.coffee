Spine = require('spine')
Ajax  = require('spine/lib/ajax')

ucfirst = (word) ->
  "#{word?[0].toUpperCase()}#{word?.substr(1)?.toLowerCase()}"

class Subscriber extends Spine.Model

  @configure 'Subscriber', 'first_name', 'last_name', 'email', 'registerDate', 'created_username', 'categories', 'languages', 'published', 'activation'

  constructor: ->
    super
    @registerDate = new Date() unless @registerDate?

  registerDateParsed: ->
    @_registerDate = Date.parse(@registerDate) unless @_registerDate
    @_registerDate

  nicename: ->
    "#{ucfirst(@first_name)} #{ucfirst(@last_name)}"

  isActive: ->
    @activation is ''

  validate: ->
    error = (key, msg) -> {key, msg}

    return error('first_name', 'First name is required') unless @first_name
    return error('last_name', 'Last name is required') unless @last_name
    return error('email', 'Email is required') unless @email
    return error('categories', 'At least one category is required') unless @categories and @categories.length > 0
    return error('languages',  'At least one language is required') unless @languages and @languages.length > 0

    false

  togglePublished: () ->
    success = true
    Ajax.disable =>
      success = @updateAttribute('published', not @published)
    @trigger('publish', @published) if success

  toggleActivation: () ->
    @trigger('activation', @isActive())

  matches: (s, category, language) =>
    res = true

    if s? and !!s # empty string
      res = res and @matchesQ(s)

    if category?
      res = res and @matchesCategory(category)

    if language?
      res = res and @matchesLanguage(language)

    res
#   @matchesQ(s) or @matchesCategory(category) or @matchesLanguage(language)

  matchesQ: (qry) ->
    qry = qry.toLowerCase()
    @first_name.toLowerCase().indexOf(qry) isnt -1 or @last_name.toLowerCase().indexOf(qry) isnt -1 or @email.toLowerCase().indexOf(qry) isnt -1

  matchesCategory: (id) =>
    for category in @categories
      return true if category.id is id

    return false

  matchesLanguage: (id) =>
    for language in @languages
      return true if language.id is id

    return false

  #

  @allCategories:[]
  @allLanguages:[]

  @setCategories: (@allCategories) -> @trigger('categories-refresh', @getCategories())
  @getCategories: ->
    @allCategories

  @setLanguages: (@allLanguages)->  @trigger('languages-refresh', @getLanguages())
  @getLanguages: ->
    @allLanguages

  @inArray: (id, array) ->
    for el in array
      return true if el['id'] is id
    return false

module.exports = Subscriber