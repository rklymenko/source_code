class ProductsController < ApplicationController
  before_action :set_product, only: [:show, :edit, :update, :destroy]
  before_action :set_categories, only: [:new, :edit]
  before_filter :set_banners, only: [:index, :show]

  # GET /products
  # GET /products.json
  def index
    @products = Product.all
  end

  # GET /products/1
  # GET /products/1.json
  def show
    @file_groups = {}
    @filter_attributes = {}
    @category = Category.find(@product.category_ids || []).first
    ProductAttribute.find(@product.product_attribute_values.pluck(:product_attribute_id))
        .map{ |pa| @filter_attributes[pa.id] = pa }
    ProductFileGroup.all.each do |product_file_group|
      @file_groups[product_file_group.title] = []
      @product.product_files.each do |product_file|
        @file_groups[product_file_group.title] << product_file if product_file.product_file_group_id == product_file_group.id
      end
    end
    save_to_recent
    set_breadcrumbs
  end

  # GET /products/new
  def new
    @product = Product.new
  end

  # GET /products/1/edit
  def edit
  end

  # POST /products
  # POST /products.json
  def create
    @product = Product.new(product_params)

    respond_to do |format|
      if @product.save
        format.html { redirect_to @product, notice: 'Product was successfully created.' }
        format.json { render :show, status: :created, location: @product }
      else
        format.html { render :new }
        format.json { render json: @product.errors, status: :unprocessable_entity }
      end
    end
  end

  # PATCH/PUT /products/1
  # PATCH/PUT /products/1.json
  def update
    respond_to do |format|
      if @product.update(product_params)
        format.html { redirect_to @product, notice: 'Product was successfully updated.' }
        format.json { render :show, status: :ok, location: @product }
      else
        format.html { render :edit }
        format.json { render json: @product.errors, status: :unprocessable_entity }
      end
    end
  end

  # DELETE /products/1
  # DELETE /products/1.json
  def destroy
    @product.destroy
    respond_to do |format|
      format.html { redirect_to products_url, notice: 'Product was successfully destroyed.' }
      format.json { head :no_content }
    end
  end

  private
    # Use callbacks to share common setup or constraints between actions.
    def set_product
      @product = Product.find(params[:id])
    end

    def set_breadcrumbs
      add_breadcrumb "Каталог товаров", '/catalogs/index'
      add_breadcrumb @category.title, @category unless @category.nil?
      add_breadcrumb @product.title
    end

    def save_to_recent
      if cookies.has_key?(:recent)
        recent = JSON.parse(cookies[:recent])
        recent.push(@product.id) unless recent.include?(@product.id)
      else
        recent = [@product.id]
      end
      cookies[:recent] = recent.to_json
    end

    def set_categories
      @categories = Category.all
    end

    # Never trust parameters from the scary internet, only allow the white list through.
    def product_params
      params.require(:product).permit(:title, :description, :price, :catalog_id)
    end
end
