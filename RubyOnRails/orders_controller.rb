class OrdersController < ApplicationController
  before_filter :authenticate_user!
  before_action :set_account_menu, only: [:user_orders]
  before_action :set_order, only: [:show, :edit, :update, :destroy]
  before_action :set_order_by_user, only: [:order_items, :print]
  skip_before_filter :authenticate_admin!, :only => [:new, :create]
  layout "print", only: [:print]

  # GET /orders
  # GET /orders.json
  def index
    @orders = Order.all
  end

  # GET /orders/1
  # GET /orders/1.json
  def show
  end

  def order
    @total = {
        amount: 0
    }
    @product_specials = ProductSpecial.all
    @basket.basket_items.each do |basket_item|
      @total[:amount] += basket_item.price.to_f * basket_item.quantity.to_i
    end
    @total[:amount] = "%.2f" % @total[:amount]
  end

  def user_orders
    add_breadcrumb "Личный кабинет", '/personal'
    add_breadcrumb "Мои заказы", '/personal/orders'
    @orders = Order.where( user_id: current_user.id)
  end

  def order_items
    render :order_items, layout: false
  end

  def print
  end

  def reclamation
    
  end

  # GET /orders/new
  def new
    @order = Order.new
    @total = {
        amount: 0
    }
    @product_specials = ProductSpecial.all
    @basket.basket_items.each do |basket_item|
      @total[:amount] += basket_item.price.to_f * basket_item.quantity.to_i
    end
    @total[:amount] = "%.2f" % @total[:amount]
  end

  # GET /orders/1/edit
  def edit
  end

  # POST /orders
  # POST /orders.json
  def create
    order_data = order_params
    order_data[:user_id] = current_user.id
    total_amount = 0
    @order = Order.new(order_data)
    @basket.basket_items.each do |basket_item|
      order_item = OrderItem.new( {
                                       product_id: basket_item.product_id,
                                       quantity: basket_item.quantity,
                                       price: basket_item.price
                                   } )
      @order.order_items << order_item
      total_amount += basket_item.price.to_f * basket_item.quantity.to_i
    end

    @order[:total_amount] = total_amount

    respond_to do |format|
      if @order.save
        @basket.destroy
        format.html { redirect_to @order, notice: 'Order was successfully created.' }
        format.json { render :show, status: :created, location: @order }
      else
        format.html { render :new }
        format.json { render json: @order.errors, status: :unprocessable_entity }
      end
    end
  end

  # PATCH/PUT /orders/1
  # PATCH/PUT /orders/1.json
  def update
    respond_to do |format|
      if @order.update(order_params)
        format.html { redirect_to @order, notice: 'Order was successfully updated.' }
        format.json { render :show, status: :ok, location: @order }
      else
        format.html { render :edit }
        format.json { render json: @order.errors, status: :unprocessable_entity }
      end
    end
  end

  # DELETE /orders/1
  # DELETE /orders/1.json
  def destroy
    @order.destroy
    respond_to do |format|
      format.html { redirect_to orders_url, notice: 'Order was successfully destroyed.' }
      format.json { head :no_content }
    end
  end

  private
    # Use callbacks to share common setup or constraints between actions.
    def set_order
      @order = Order.find(params[:id])
    end

    def set_order_by_user
      @order = Order.find_by( { user_id: current_user.id, id: params[:order_id] } )
    end

    # Never trust parameters from the scary internet, only allow the white list through.
    def order_params
      params.require(:order).permit(:comment)
    end
end
