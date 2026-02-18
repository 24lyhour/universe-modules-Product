import { z } from 'zod';

export const addOnSchema = z.object({
    add_on_product_id: z.number({
        required_error: 'Product is required',
    }).nullable(),
    price_adjustment: z.number().default(0),
    max_quantity: z.number().min(1, 'Max quantity must be at least 1').default(1),
    sort_order: z.number().min(0).default(0),
    is_required: z.boolean().default(false),
    is_active: z.boolean().default(true),
});

export type AddOnSchemaType = z.infer<typeof addOnSchema>;
