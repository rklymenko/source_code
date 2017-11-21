@Products = React.createClass
  getInitialState: ->
    products: @props.data
  getDefaultProps: ->
    products: []
  addProduct: (product) ->
    products = React.addons.update(@state.products, { $push: [product] })
    @setState products: products
  credits: ->
    credits = @state.products.filter (val) -> val.price >= 0
    credits.reduce ((prev, curr) ->
      prev + parseFloat(curr.price)
    ), 0
  debits: ->
    debits = @state.products.filter (val) -> val.price < 0
    debits.reduce ((prev, curr) ->
      prev + parseFloat(curr.price)
    ), 0
  balance: ->
    @debits() + @credits()
  deleteProduct: (product) ->
    index = @state.products.indexOf product
    products = React.addons.update(@state.products, { $splice: [[index, 1]] })
    @replaceState products: products
  render: ->
    React.DOM.div
      className: 'products'
      React.DOM.h1
        className: 'title'
        'Products'
      React.DOM.div
        className: 'row'
        React.createElement AmountBox, type: 'success', amount: @credits(), text: 'Credit'
        React.createElement AmountBox, type: 'danger', amount: @debits(), text: 'Debit'
        React.createElement AmountBox, type: 'info', amount: @balance(), text: 'Balance'
      React.createElement ProductForm, handleNewProduct: @addProduct
      React.DOM.hr null
      React.DOM.table
        className: 'table table-bordered'
        React.DOM.thead null,
          React.DOM.tr null,
            React.DOM.th null, 'Title'
            React.DOM.th null, 'Price'
            React.DOM.th null, 'Actions'
        React.DOM.tbody null,
          for product in @state.products
            React.createElement Product, key: product.id, product: product, handleDeleteProduct: @deleteProduct
