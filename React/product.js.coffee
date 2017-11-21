@Product = React.createClass
  handleDelete: (e) ->
    e.preventDefault()
    $.ajax
      method: 'DELETE'
      url: "/products/#{ @props.product.id }"
      dataType: 'JSON'
      success: () =>
        @props.handleDeleteProduct @props.product
  render: ->
    React.DOM.tr null,
      React.DOM.td null, @props.product.title
      React.DOM.td null, amountFormat(@props.product.price)
      React.DOM.td null,
        React.DOM.a
          className: 'btn btn-danger'
          onClick: @handleDelete
          'Delete'