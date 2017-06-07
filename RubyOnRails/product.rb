class Product
  include Mongoid::Document
  field :title
  field :reference
  field :analog
  field :rest
  field :article
  field :rest_indicate
  field :own_prod, type: Boolean
  field :show_old_price, type: Boolean
  field :accessory, type: Array
  field :associated, type: Array
  field :barcode
  field :product_file_ids, type: Array
  field :meta_description, default: ''
  field :description, default: ''
  field :product_image_ids, type: Array
  field :number, default: ''
  field :price, type: BigDecimal, default: 0.0
  field :old_price, type: BigDecimal, default: 0.0
  field :category_ids, type: Array
  field :product_attribute_value_ids, type: Array
  field :rating, type: Integer
  field :order, type: Integer

  has_and_belongs_to_many :categories
  has_and_belongs_to_many :product_attribute_values
  has_and_belongs_to_many :product_bestsellers
  has_and_belongs_to_many :product_specials
  has_and_belongs_to_many :product_isnws
  has_and_belongs_to_many :product_images
  has_and_belongs_to_many :product_files
  has_and_belongs_to_many :product_file_groups
  has_one :basket_item
  has_one :order_item
  
  # Returns Product's first image URL.
  #
  # @return <String>
  def image_url
    if product_image_ids.count > 0
      product_images.first.path
    else
      ActionController::Base.helpers.asset_path('catalog/no-image.png')
    end
  end

  def self.import_products products, category_ids
    products_ref_ids ={}
    products = [products] if products.is_a?(Hash)
    products.each do |section_product|
      products_ref_ids[section_product["id"]] = section_product["reference"]
      product = find_or_create_by( id: section_product["reference"] )
      product.reference = section_product["reference"]
      product.title = section_product["title"]
      product.analog = section_product["analog"]
      product.price = section_product["price"].to_f
      product.rest = section_product["rest"]
      product.article = section_product["article"]
      product.rest_indicate = section_product["rest_indicate"]
      product.own_prod = section_product["own_prod"].to_i
      product.show_old_price = section_product["show_old_price"].to_i
      product.barcode = section_product["barcode"]
      product.meta_description = section_product["meta_description"]
      product.old_price = section_product["old_price"].to_f
      product.rating = section_product["rating"].to_i
      product.category_ids = category_ids
      product.order = section_product["order"]

      unless section_product["category"].nil?
        product_attributes = section_product["category"].split(",")
        product_attributes = [product_attributes] unless product_attributes.is_a?(Array)
        product_attributes.each do |product_attribute|
          product.product_attribute_value_ids << product_attribute.strip
        end
      end

      unless section_product["photos"].nil?
        product_images = section_product["photos"].split(",")
        product_images = [product_images] unless product_images.is_a?(Array)
        product_images.each do |product_image|
          product.product_image_ids << product_image.strip
        end
      end

      unless section_product["files"].nil?
        product_files = section_product["files"].split(",")
        product_files = [product_files] unless product_files.is_a?(Array)
        product_files.each do |product_image|
          product.product_file_ids << product_image.strip
        end
      end

      product.save!
    end
    products_ref_ids
  end

  def self.number_per_category
    Product.collection.aggregate([
      {"$unwind" => "$category_ids"},
      {"$project" =>
        {
          "category_id" => "$category_ids"
        }
      },
      { "$group" => { "_id" => "$category_id", "count" => { "$sum" => 1 } }}
    ]).to_a.inject({}) do |hash, category|
      hash[category["_id"].to_i] = category["count"]
      hash
    end
  end
end
