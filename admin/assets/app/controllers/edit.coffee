Spine = require('spine')
Subscriber = require('models/subscriber')
$ = Spine.$

errorMsg = (key, msg) -> {key, msg}
validateEmpty = (value) -> value? and value.trim() isnt ''
validateEmail = (email) -> /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(email)

$.fn.serializeForm = ->
  res = {}
  for item in $(@).serializeArray()
    name = String::replace.apply(item.name, [/\[\]$/, ''])
    value = item.value
    if /\[\]$/.test(item.name)
      (res[name] ||= []).push(value)
    else
      res[name] = value
  res

class Edit extends Spine.Controller

  @include Spine.Log

  className: 'edit controller'

  @validations:
    first_name: validateEmpty
    last_name: validateEmpty
    email:  (email) -> validateEmpty(email) and validateEmail(email)

  @messages:
    first_name: 'First name is required'
    last_name: 'Last name is required'
    email:  'A valid email is required'

  blured: (e) =>
    if @constructor.validations[e.target.id] and @constructor.validations[e.target.id]($(e.target).val())
      clearMsg $(e.target)
    else
      printErrorMsg $(e.target), @constructor.messages[e.target.id]

  events:
    'submit form'           : 'submit'
    'click .cancel'         : 'cancel'
    'change #first_name'    : 'blured'
    'change #last_name'     : 'blured'
    'change #email'         : 'blured'

  elements:
    'form': 'form'
    '#subscribeformsubmit': 'submitBtn'

  constructor:->
    super
    @active @change

    Subscriber.bind 'error', @error
    Subscriber.bind 'categories-refresh languages-refresh', => @render() if @isActive()

  tmpl: require('views/form')

  render:->
    @html @tmpl(@item)
    @submitBtn.button()

  change:(params) =>
    try
      @item = if params.id then Subscriber.find(params.id) else new Subscriber({})
      @render()
    catch msg
      @log "Error:#{msg}"
      printErrorMsg "Error:#{msg}"
      false # @navigate('/subscribers')

  submit:(e) ->
    e.preventDefault()
    params = @form.serializeForm()

    if not validateEmpty(params['first_name'])
      @item.trigger('error', errorMsg('first_name', 'First name is required'))
      return false

    if not validateEmpty(params['last_name'])
      @item.trigger('error', errorMsg('last_name', 'Last name is required'))
      return false

    if not validateEmpty(params['email'])
      @item.trigger('error', errorMsg('email', 'Email is required'))
      return false

    if not validateEmail(params['email'])
        @item.trigger('error', errorMsg('email', 'A valid email is required'))
        return false

    if not params['categories']
      @item.trigger('error', errorMsg('categories', 'At least one category is required'))
      return false

    if not params['languages']
      @item.trigger('error', errorMsg('languages',  'At least one language is required'))
      return false

    @submitBtn.button('loading')

    data = {}
    data['id'] = @item.id unless @item.isNew()
    data['email'] = params['email']

    $.ajax(
      url:  @constructor.urlForTask('subscriber.check_email')
      dataType: 'json'
      data: data
    ).success (result) =>

        @submitBtn.button('reset')

        if result['success'] is true # means that the email is available


          @navigate('/subscribers') if @item.updateAttributes(params)

        else

          @item.trigger('error', errorMsg('email', 'This email has already been registered'))

    .error =>

       printErrorMsg null, 'An unknown error occured, please try again'

    false

  cancel: (e) ->
    e.preventDefault()
    @navigate('/subscribers')

  error: (record, error) =>
    if record.eql @item
      @log "Its my record"
      printErrorMsg $("##{error.key}"), error.msg

    if @isActive()
      @log "error: I am active"

    @log "Error:#{JSON.stringify(error)}"

  #

  @urlForTask:(task) ->
    "#{baseurl}" + (if lang then "/#{lang}?" else '?') + $.param({option:'com_newsletter', task:task, format:'raw'})

module.exports = Edit