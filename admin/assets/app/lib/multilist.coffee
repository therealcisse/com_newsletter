Spine ?= require('spine')
$      = Spine.$

class Spine.MultiList extends Spine.Controller
  events:
    'click .item': 'click'

  constructor: ->
    super

    @bind 'select', (list, items...) =>
      @toggleSelections(items...)

  model: null

  clear: ->
    @el.empty() # maybe use specialized collections to keep track of @items and @selectedItems to avoid this and @append(...)
    @items = []

  # re-renders the entire list
  render: (items) ->
    @clear()

    @items = items if items
    for item in @items
      view = new @model(item: item)
      @append(view.render().el)

    @delay (=> @select(i) for i in @selectedItems) #, 1000

  children: (sel) ->
    @el.children(sel)

  click: (e) ->
    item = $(e.currentTarget).item()
    @trigger('select', @, item)

  selectedItems: []

  getSelectedItems: ->
    @selectedItems

  @indexOfItem: (items, item) ->
    len = items.length
    return -1 if not len

    i = 0
    while i < len
      if i not of items
        continue

      if item is items[i] or item.eql items[i]
        return i
      i++
    -1

  toggleSelections: (items...) ->
    for item in items
      index = @constructor.indexOfItem(@selectedItems, item)
      if index is -1 then @select(item) else @unselect(item)

  unselect: (item) =>
    index = @constructor.indexOfItem(@selectedItems, item)

    if index > -1
      try
        @children().forItem(item).removeClass('selected') # this will cause an exception if the trigger was a delete because of call to `reload` in forItem
      catch msg
        console.log("Exception in unselect: #{msg}")
      finally
        @selectedItems.splice(index, 1)

  select: (item) =>
    @selectedItems.push(item) if @constructor.indexOfItem(@selectedItems, item) is -1 # no dups
    @children().forItem(item).addClass('selected')

  isSelected: (item) ->
    @constructor.indexOfItem(@selectedItems, item) > -1

module?.exports = Spine.MultiList