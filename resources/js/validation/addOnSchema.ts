import { z } from 'zod';

// Schema for nested add-on creation (product context known)
export const addOnSchema = z.object({
    add_on_product_id: z.number().nullable().optional(),
    name: z.string({
        required_error: 'Add-on name is required',
    }).min(1, 'Add-on name is required').max(255, 'Name must be 255 characters or less'),
    description: z.string().optional().default(''),
    image_url: z.string().optional().default(''),
    price_adjustment: z.number({
        required_error: 'Price adjustment is required',
    }),
    max_quantity: z.number({
        required_error: 'Max quantity is required',
    }).min(1, 'Max quantity must be at least 1'),
    sort_order: z.number().min(0).default(0),
    is_required: z.boolean({
        required_error: 'Required field is required',
    }),
    is_active: z.boolean({
        required_error: 'Active field is required',
    }),
});

// Schema for standalone add-on creation (must select parent product)
export const addOnStandaloneSchema = z.object({
    product_id: z.number({
        required_error: 'Parent product is required',
    }).min(1, 'Please select a parent product'),
    name: z.string({
        required_error: 'Add-on name is required',
    }).min(1, 'Add-on name is required').max(255, 'Name must be 255 characters or less'),
    description: z.string().optional().default(''),
    image_url: z.string().optional().default(''),
    price_adjustment: z.number({
        required_error: 'Price adjustment is required',
    }),
    max_quantity: z.number({
        required_error: 'Max quantity is required',
    }).min(1, 'Max quantity must be at least 1'),
    sort_order: z.number().min(0).default(0),
    is_required: z.boolean({
        required_error: 'Required field is required',
    }),
    is_active: z.boolean({
        required_error: 'Active field is required',
    }),
});

export type AddOnSchemaType = z.infer<typeof addOnSchema>;
export type AddOnStandaloneSchemaType = z.infer<typeof addOnStandaloneSchema>;
