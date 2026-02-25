<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Modules\Product\Actions\Dashboard\V1\UpdateProductSettingsAction;
use Modules\Product\Http\Requests\Dashboard\V1\Settings\UpdateProductSettingsRequest;

class ProductSettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('product::dashboard/settings/ProductSettings', [
            'productSettings' => UpdateProductSettingsAction::getSettings(),
        ]);
    }

    public function update(UpdateProductSettingsRequest $request, UpdateProductSettingsAction $action)
    {
        $action->execute($request->validated());

        return back()->with('success', 'Product settings updated successfully.');
    }
}
