import { z } from 'zod';
import { toTypedSchema } from '@vee-validate/zod';

// Zod schema for Brand form validation
export const brandSchema = z.object({
    name: z
        .string({ required_error: 'Brand name is required' })
        .min(1, 'Brand name is required')
        .max(255, 'Brand name must be less than 255 characters'),
    description: z.string().optional().nullable(),
    logo: z
        .string()
        .max(500, 'Logo URL must be less than 500 characters')
        .optional()
        .nullable(),
    website: z
        .string()
        .url('Please enter a valid URL')
        .max(500, 'Website URL must be less than 500 characters')
        .optional()
        .nullable()
        .or(z.literal('')),
    outlet_id: z.number().int().optional().nullable(),
    sort_order: z
        .number()
        .int('Sort order must be a whole number')
        .min(0, 'Sort order cannot be negative')
        .default(0),
    is_active: z.boolean().default(true),
});

// TypedSchema for vee-validate
export const brandValidationSchema = toTypedSchema(brandSchema);

// Type inference from Zod schema
export type BrandFormValues = z.infer<typeof brandSchema>;
