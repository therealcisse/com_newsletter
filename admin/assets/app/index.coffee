require('lib/setup')

Spine = require('spine')
Ajax  = require('spine/lib/ajax')
Manager = require('spine/lib/manager')

Subscriber = require('models/subscriber')
Subscribers = require('controllers/subscribers')
Index = require('controllers/index')
Edit = require('controllers/edit')

class App extends Spine.Controller

  @include Spine.Log

  constructor:->
    super

    Subscriber.bind("create", @addOne)
    Subscriber.bind("refresh", @addAll)

    Subscriber.bind("create", @create)
    Subscriber.bind("update", @update)
    Subscriber.bind("destroy", @destroy)
    Subscriber.bind("publish", @togglePublished)

    Subscriber.bind("activation", @toggleActivation)

    @edit = new Edit
    @index = new Index

    @manager = new Manager(@index, @edit)

    @index.toolsbar.bind 'reload', @load
    @index.toolsbar.bind 'bulk', @doBulkAction

    @index.toolsbar.bind 'sort', @refreshUi
    @index.filters.bind 'search', @refreshUi

    Subscriber.bind "destroy", @index.subscriberList.unselect

    @routes
      '/subscriber/:id': (params) =>
        @edit.active(params)

      '/subscribers': (params) =>
        @index.active(params)

      '/subscribers/new': () =>
        @edit.active({})

    @append @index.active(), @edit

    (do @load).done =>
        Spine.Route.setup()

  load: =>
    printSuccessMsg 'Loading...'
    @fetchAll().done (categories, languages, subscribers) =>
      printSuccessMsg 'Done loading!'
      Subscriber.refresh(subscribers, clear:true)
      Subscriber.setCategories(categories)
      Subscriber.setLanguages(languages)

  fetchAll: ->
    @log "FetchAll"

    reporter = $.Deferred()
    $.when(
      $.getJSON(
        @constructor.urlForTask('subscribers.getCategories')
      ),
      $.getJSON(
        @constructor.urlForTask('subscribers.getLanguages')
      ),
      $.getJSON(
        @constructor.urlForTask('subscribers.display')
      )
    ).done (categories, languages, subscribers) =>
        reporter.resolve categories[0]['results'], languages[0]['results'], subscribers[0]['results']
     .fail =>
        reporter.reject arguments...

    reporter

  addOne:(item) =>
    if @index.filters.matches(item)
      view = new Subscribers(item:item)
      @index.subscriberList.prepend(view.render().el)

  addAll: (records) =>
    if records is false then @refreshUi() else @addOne(record) for record in records # just add the new set

  refreshUi: =>
    @index.subscriberList.render(record for record in @filtered @sorted Subscriber.all())

  filtered: (records) ->
    @index.filters.filtered(records)

  sorted: (records) ->
    stringcmp = (getter) ->
      (lhs, rhs) ->
        a = getter(lhs).toLowerCase()
        b = getter(rhs).toLowerCase()
        return 1 if a > b
        return -1 if a < b
        0

    fn =
      switch @index.toolsbar.getSortKey()
        when 'recent'   then (lhs, rhs) -> rhs.registerDateParsed() - lhs.registerDateParsed()
        when 'name'     then stringcmp (record) -> record.nicename()
        when 'email'    then stringcmp (record) -> record.email
        when 'category' then (lhs, rhs) -> rhs.categories.length - lhs.categories.length
        when 'language' then (lhs, rhs) -> rhs.languages.length - lhs.languages.length
        else (lhs, rhs) -> rhs.registerDateParsed() - lhs.registerDateParsed()


    records.sort(fn)

  create: (record) =>
    Ajax.queue =>
      data = record.toJSON()
      delete data['id'] # this is not really conventional
      $.ajax(
        url:@constructor.urlForTask('subscriber.save')
        type:'POST'
        dataType:'json'
        data:data
      ).success (data) =>

          if data['success'] is true

            ret = data['results']
            Ajax.disable ->
              record.changeID(ret['id'])
              record.updateAttributes(ret)
              printSuccessMsg 'Successfully created'

          else

            printSuccessMsg('there was an error while creating subscription, pleas etry again')

      .error =>
          printSuccessMsg('there was an error while creating subscription, pleas etry again')

  update: (record) =>
    Ajax.queue =>
      $.ajax(
        url:@constructor.urlForTask('subscriber.save')
        type:'POST'
        dataType:'json'
        data:record.toJSON()
      ).success (data) =>

          if data['success'] is true

            Ajax.disable ->
              record.updateAttributes(data['results'])
              printSuccessMsg('Subscription updated')

          else

            @log "#{JSON.stringify(data['error'])}"
            printSuccessMsg('there was an error while updating the subscription, pleas etry again')

      .error =>
          printSuccessMsg('there was an error while updating subscription, pleas etry again')

  destroy: (record) =>
    Ajax.queue =>
      $.ajax(
          url:@constructor.urlForTask('subscriber.destroy')
          type:'POST'
          data:
            id:record.id
       ).error =>

            printSuccessMsg('there was an error while deleting the subscription, pleas etry again')
            @log('Error deleting')

        .success =>

           printSuccessMsg 'Subscription deleted!'
           @log("Successfully deleted #{record.nicename()}")

  togglePublished: (record, publish) =>
    $.ajax(
      url:@constructor.urlForTask('subscriber.' + (if publish is true then 'publish' else 'unpublish'))
      type:'POST'
      data:
        id:record.id
    ).success =>

        printSuccessMsg "Subscription #{if publish then 'published' else 'unpublished'}"
        @log "Published #{record.nicename()}"

     .error =>

        printSuccessMsg("there was an error while #{if publish then 'publishing' else 'unpublishing'} the subscription, pleas etry again")
        @log('Error publishing')

     .complete =>
        @index.subscriberList.select(record) if @index.subscriberList.isSelected(record)

  toggleActivation: (record, activated) =>
    $.ajax(
      url:@constructor.urlForTask('subscriber.' + (if activated is true then 'deactivate' else 'activate'))
      type:'POST'
      dataType:'json'
      data:
        id:record.id
    ).success (data) =>

        printSuccessMsg "Subscription #{if activated then 'deactivated' else 'activated'}"

        if data['success'] is true

          Ajax.disable =>
            record.updateAttributes(data['results'])

        else

          printSuccessMsg("Unable to #{if publish then 'activate' else 'deactivate'} subscription, pleas etry again")

     .error =>

        printSuccessMsg("there was an error while #{if activated then 'activating' else 'deactivating'} the subscription, pleas etry again")
        @log('Error activating')

     .complete =>
        lst = @index.subscriberList
        lst.select(record) if lst.isSelected(record)

  doBulkAction: (action) =>
    switch action
      when 'delete'

        @log 'Action: delete'
        @log "SelectedItems:#{@index.subscriberList.getSelectedItems()}"

        if confirm('Are you sure?')
          record.destroy() for record in Subscriber.cloneArray @index.subscriberList.getSelectedItems() # we must clone cuz the values will be removed from the array and that will cuz index issues

      when 'publish', 'unpublish'

        @log "Action: #{action}"
        @log "SelectedItems:#{@index.subscriberList.getSelectedItems()}"

        condPublish =
          if action is 'publish'
            (record) => record.isActive() and not record.published
          else
            (record) => record.isActive() and record.published

        if confirm('Are you sure?')
          record.togglePublished() for record in @index.subscriberList.getSelectedItems() when condPublish(record)

      when 'activate', 'deactivate'

        @log "Action: #{action}"
        @log "SelectedItems:#{@index.subscriberList.getSelectedItems()}"

        condActivate =
          if action is 'activate'
            (record) => not record.isActive()
          else
            (record) => record.isActive()

        if confirm('Are you sure?')
          record.toggleActivation() for record in @index.subscriberList.getSelectedItems() when condActivate(record)

  #

  @urlForTask:(task) ->
    "#{baseurl}" + (if lang then "/#{lang}?" else '?') + $.param({option:'com_newsletter', task:task, format:'raw'})

class Tester extends Spine.Controller

  constructor: ->
    super
    @render()

  render: ->
    @html require('views/filters')

module.exports = App
    