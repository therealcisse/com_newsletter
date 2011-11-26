Spine = require('spine')
Subscriber = require('models/subscriber')
$ = Spine.$

class Subscribers extends Spine.Controller

  @include Spine.Log

  events:
    "click .js-action-remove"               : "destroy"
    "click .subscriber-state"               : "togglePublished"
    "click .js-action-edit"                 : "edit"
    "click .js-action-toggle-activation"    : "toggleActivation"
    'click .subscriber-fullname'            : 'edit'

  elements:
    '.subscriber-select': 'checkbox'

  constructor: ->
    super

    @item.bind('destroy', @release)
    @item.bind('change', (record, type) => (@render() if record.eql(@item)) unless type is 'destroy')

  tmpl: require('views/item')

  render:=>
    @item.reload() # spine clones a lot

    @replace $ @tmpl(@item)

    # set states
    @el.toggleClass('published', @item.published)
    @el.toggleClass('deactivated', not @item.isActive())

    @el.data('item', @item)
    @

  edit:->
    @navigate('/subscriber', @item.id)
    false

  destroy:->
    @item.destroy() if confirm('Are you sure?')
    false

  togglePublished:->
    if not @item.isActive()
      @log("Can't #{if @item.published then 'publish' else 'unpublish'} an unactivated subscriber")
      return false
    @item.togglePublished() if confirm('Are you sure?')
    false

  toggleActivation: () ->
    @item.toggleActivation() if confirm('Are you sure?')
    false

module.exports = Subscribers