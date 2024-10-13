<?php

namespace Tests\Unit;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
class ItemTest extends TestCase
{

    /**
     * A basic unit test example.
     */
    public function testCreateItem(): void
    {
        $user = User::factory()->create([
            'isAdmin' => true
        ]);
        $this->actingAs($user);
        $itemController = new \App\Http\Controllers\Api\ItemController;
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemName' => 'Sample Item',
            'itemCode' => 'AAA1',
        ]);
        $createItem = $itemController->store($request);
        $this->assertDatabaseHas('items',[
            'itemName' => 'Sample Item',
            'itemCode' => 'AAA1'
        ]);
        $itemData = $createItem->getData();
        $item = Item::find($itemData->data->id);
        $item->delete();
        $user->delete();

    }
}
