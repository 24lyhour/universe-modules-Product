import { z } from 'zod';
import { toTypedSchema } from '@vee-validate/zod';

// Zod schema for Product form validation
export const productSchema = z.object({
    name: z
        .string({ required_error: 'Product name is required' })
        .min(1, 'Product name is required')
        .max(255, 'Product name must be less than 255 characters'),
    description: z.string().optional().nullable(),
    sku: z
        .string()
        .max(100, 'SKU must be less than 100 characters')
        .optional()
        .nullable(),
    product_type: z
        .enum(['phone', 'computer', 'tablet', 'accessory', 'other'])
        .optional()
        .nullable(),
    price: z
        .number({ required_error: 'Price is required' })
        .min(0, 'Price must be at least 0'),
    purchase_price: z
        .number()
        .min(0, 'Purchase price must be at least 0')
        .optional()
        .nullable(),
    sale_price: z
        .number()
        .min(0, 'Sale price must be at least 0')
        .optional()
        .nullable(),
    stock: z
        .number({ required_error: 'Stock is required' })
        .int('Stock must be a whole number')
        .min(0, 'Stock cannot be negative'),
    low_stock_threshold: z
        .number()
        .int('Low stock threshold must be a whole number')
        .min(0, 'Low stock threshold cannot be negative')
        .optional()
        .nullable(),
    status: z.enum(['active', 'inactive', 'draft', 'out_of_stock'], {
        required_error: 'Status is required',
    }),
    is_featured: z.boolean().default(false),
    pre_order: z.boolean().default(false),
    images: z.array(z.string()).optional().nullable(),
    category_id: z.number().int().optional().nullable(),
    outlet_id: z.number().int().optional().nullable(),
});

// TypedSchema for vee-validate
export const productValidationSchema = toTypedSchema(productSchema);

// Type inference from Zod schema
export type ProductFormValues = z.infer<typeof productSchema>;
