// Product Module Types

export type ProductType = 'phone' | 'computer' | 'tablet' | 'accessory' | 'other';
export type AttributeType = 'select' | 'color' | 'button';

export interface ProductVariantAttributeValue {
    id: number;
    value: string;
    label: string | null;
    color_code: string | null;
    attribute: {
        id: number;
        name: string;
        type: AttributeType;
    } | null;
}

export interface ProductVariantSimple {
    id: number;
    uuid: string;
    sku: string | null;
    name: string | null;
    price: number | null;
    sale_price: number | null;
    stock: number;
    is_default: boolean;
    is_active: boolean;
    images: string[];
    attribute_values: Record<string, string> | null;
    attribute_value_relations?: ProductVariantAttributeValue[];
}

export interface ProductAttributeSimple {
    id: number;
    uuid: string;
    name: string;
    type: AttributeType;
    is_active: boolean;
    values: {
        id: number;
        value: string;
        label: string | null;
        color_code: string | null;
        is_active: boolean;
    }[];
}

export interface Product {
    id: number;
    uuid: string;
    name: string;
    slug: string;
    description: string | null;
    sku: string | null;
    product_type: ProductType | null;
    price: number;
    purchase_price: number | null;
    sale_price: number | null;
    effective_price: number;
    discount_percentage: number | null;
    is_on_sale: boolean;
    stock: number;
    low_stock_threshold: number;
    is_in_stock: boolean;
    is_low_stock: boolean;
    status: 'active' | 'inactive' | 'draft' | 'out_of_stock';
    is_featured: boolean;
    pre_order: boolean;
    images: string[];
    category_id: number | null;
    category: ProductCategory | null;
    outlet_id: number | null;
    outlet: Outlet | null;
    upsale_id: number | null;
    upsell?: ProductSimple | null;
    down_sale_id: number | null;
    downsell?: ProductSimple | null;
    created_by: number | null;
    updated_by: number | null;
    variants_count?: number;
    attributes_count?: number;
    has_variants?: boolean;
    has_attributes?: boolean;
    variants?: ProductVariantSimple[];
    attributes?: ProductAttributeSimple[];
    created_at: string;
    updated_at: string;
}

export interface ProductCategory {
    id: number;
    name: string;
}

export interface ProductSimple {
    id: number;
    name: string;
    price: number;
    sku: string | null;
}

export interface Outlet {
    id: number;
    name: string;
}

export interface ProductStats {
    total: number;
    active: number;
    inactive: number;
    draft: number;
    out_of_stock: number;
    low_stock: number;
    featured: number;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    path?: string;
}

export interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: PaginationMeta;
    links?: PaginationLinks;
}

export interface ProductFilters {
    status?: string;
    search?: string;
    category_id?: number;
    outlet_id?: number;
    product_type?: string;
    is_featured?: boolean;
    in_stock?: boolean;
    low_stock?: boolean;
    min_price?: number;
    max_price?: number;
}

// Form Data Types
export interface ProductFormData {
    name: string;
    description: string;
    sku: string;
    product_type: ProductType | null;
    price: number;
    purchase_price: number | null;
    sale_price: number | null;
    stock: number;
    low_stock_threshold: number;
    status: 'active' | 'inactive' | 'draft' | 'out_of_stock';
    is_featured: boolean;
    pre_order: boolean;
    images: string[];
    category_id: number | null;
    outlet_id: number | null;
    upsale_id: number | null;
    down_sale_id: number | null;
}

// Page Props Types
export interface ProductIndexProps {
    products: PaginatedResponse<Product>;
    filters: ProductFilters;
    stats: ProductStats;
    outlets?: Outlet[];
}

export interface ProductShowProps {
    product: Product;
}

export interface ProductCreateProps {
    categories?: ProductCategory[];
    outlets?: Outlet[];
    products?: ProductSimple[];
}

export interface ProductEditProps {
    product: Product;
    categories?: ProductCategory[];
    outlets?: Outlet[];
    products?: ProductSimple[];
}

// ==================== VARIATION TYPES ====================

export interface ProductAttribute {
    id: number;
    uuid: string;
    name: string;
    slug: string;
    type: AttributeType;
    description: string | null;
    sort_order: number;
    is_active: boolean;
    values?: ProductAttributeValue[];
    values_count?: number;
    created_at: string;
    updated_at: string;
}

export interface ProductAttributeValue {
    id: number;
    uuid: string;
    attribute_id: number;
    value: string;
    label: string | null;
    color_code: string | null;
    image_url: string | null;
    price_adjustment: number;
    sort_order: number;
    is_active: boolean;
    attribute?: ProductAttribute;
    display_label?: string;
    formatted_price_adjustment?: string;
}

export interface ProductVariant {
    id: number;
    uuid: string;
    product_id: number;
    sku: string | null;
    name: string | null;
    price: number | null;
    purchase_price: number | null;
    sale_price: number | null;
    stock: number;
    low_stock_threshold: number;
    barcode: string | null;
    weight: number | null;
    images: string[] | null;
    attribute_values: Record<string, string> | null;
    is_default: boolean;
    is_active: boolean;
    sort_order: number;
    product?: Product;
    attribute_value_relations?: ProductAttributeValue[];
    effective_price?: number;
    display_price?: number;
    is_in_stock?: boolean;
    is_low_stock?: boolean;
    is_on_sale?: boolean;
    created_at: string;
    updated_at: string;
}

// ==================== UPSELL TYPES ====================

export type UpsellType = 'upsell' | 'downsell' | 'cross_sell';

export interface ProductUpsell {
    id: number;
    uuid: string;
    product_id: number;
    upsell_product_id: number;
    type: UpsellType;
    type_label: string;
    discount_percentage: number | null;
    discounted_price: number | null;
    sort_order: number;
    is_active: boolean;
    upsell_product?: {
        id: number;
        uuid: string;
        name: string;
        slug: string;
        sku: string | null;
        price: number;
        sale_price: number | null;
        effective_price: number;
        is_on_sale: boolean;
        stock: number;
        is_in_stock: boolean;
        images: string[];
        status: string;
    };
    created_at: string;
    updated_at: string;
}

export interface ProductUpsellFormData {
    upsell_product_id: number | null;
    type: UpsellType;
    discount_percentage: number | null;
    sort_order: number;
    is_active: boolean;
}

export interface ProductUpsellStats {
    total: number;
    upsells: number;
    downsells: number;
    cross_sells: number;
    active: number;
}

export interface ProductUpsellIndexProps {
    product: ProductSimple;
    upsells: ProductUpsell[];
    availableProducts: ProductSimple[];
    stats: ProductUpsellStats;
}

export interface ProductUpsellCreateProps {
    product: ProductSimple;
    availableProducts: ProductSimple[];
}

export interface ProductUpsellEditProps {
    product: ProductSimple;
    upsell: ProductUpsell;
}

// ==================== ADD-ON TYPES ====================

export interface ProductAddOn {
    id: number;
    uuid: string;
    product_id: number;
    add_on_product_id: number;
    price_adjustment: number;
    formatted_price_adjustment: string;
    final_price: number;
    max_quantity: number;
    sort_order: number;
    is_required: boolean;
    is_active: boolean;
    add_on_product?: {
        id: number;
        uuid: string;
        name: string;
        slug: string;
        sku: string | null;
        price: number;
        sale_price: number | null;
        effective_price: number;
        is_on_sale: boolean;
        stock: number;
        is_in_stock: boolean;
        images: string[];
        status: string;
    };
    created_at: string;
    updated_at: string;
}

export interface ProductAddOnFormData {
    add_on_product_id: number | null;
    price_adjustment: number;
    max_quantity: number;
    sort_order: number;
    is_required: boolean;
    is_active: boolean;
}

export interface ProductAddOnStats {
    total: number;
    required: number;
    optional: number;
    active: number;
}

export interface ProductAddOnIndexProps {
    product: ProductSimple;
    addOns: ProductAddOn[];
    availableProducts: ProductSimple[];
    stats: ProductAddOnStats;
}

export interface ProductAddOnCreateProps {
    product: ProductSimple;
    availableProducts: ProductSimple[];
}

export interface ProductAddOnEditProps {
    product: ProductSimple;
    addOn: ProductAddOn;
}

// Extended Product interface with variations
export interface ProductWithVariations extends Product {
    variants?: ProductVariant[];
    attributes?: ProductAttribute[];
    has_variants?: boolean;
    has_attributes?: boolean;
    total_variant_stock?: number;
    price_range?: {
        min: number;
        max: number;
    };
    default_variant?: ProductVariant | null;
}

// Attribute Form Data
export interface ProductAttributeFormData {
    name: string;
    type: AttributeType;
    description: string;
    sort_order: number;
    is_active: boolean;
}

export interface ProductAttributeValueFormData {
    value: string;
    label: string;
    color_code: string;
    image_url: string;
    price_adjustment: number;
    sort_order: number;
    is_active: boolean;
}

export interface ProductVariantFormData {
    product_id: number;
    sku: string;
    name: string;
    price: number | null;
    purchase_price: number | null;
    sale_price: number | null;
    stock: number;
    low_stock_threshold: number;
    barcode: string;
    weight: number | null;
    images: string[];
    is_default: boolean;
    is_active: boolean;
    sort_order: number;
    attribute_value_ids: number[];
}

// Page Props for Attributes
export interface ProductAttributeIndexProps {
    attributes: PaginatedResponse<ProductAttribute>;
    filters: {
        search?: string;
        type?: string;
        is_active?: boolean;
    };
    stats: {
        total: number;
        active: number;
        inactive: number;
    };
}

export interface ProductAttributeShowProps {
    attribute: ProductAttribute;
}

export interface ProductAttributeCreateProps {
    // empty for now
}

export interface ProductAttributeEditProps {
    attribute: ProductAttribute;
}

// Page Props for Variants
export interface ProductVariantIndexProps {
    product: Product;
    variants: PaginatedResponse<ProductVariant>;
    attributes: ProductAttribute[];
}

export interface ProductVariantShowProps {
    product: Product;
    variant: ProductVariant;
}

export interface ProductVariantCreateProps {
    product: Product;
    attributes: ProductAttribute[];
}

export interface ProductVariantEditProps {
    product: Product;
    variant: ProductVariant;
    attributes: ProductAttribute[];
}
