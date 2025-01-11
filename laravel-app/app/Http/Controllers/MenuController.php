<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $queryMenu = Menu::where('company_id', $companyId);
        $params = $request["search"];
        if ($params["name"]) {
            $queryMenu->where('name', 'like', '%' . $params['name'] . '%');
        }
        $currentPage = $params['currentPage'];
        $menus = $queryMenu->paginate(14, ['*'], 'page', $currentPage);
        $menuIds = $menus->pluck('id')->toArray();
        return response()->json(
            [
                'success' => true,
                'menus' => $menus->map(function ($menu){
                    return[
                        'id' => $menu->id,
                        'name' => $menu->name,
                        'updatedAt' => Carbon::parse($menu->updated_at)->format('Y年m月d日'),
                    ];
                }),
                'ids' => $menuIds,
                'lastPage' => $menus->lastPage(),
            ]
        );
    }

    public function store(Request $request)
    {
        $menu = $request['menu'];

        $user = Auth::user();

        $createdMenu = Menu::create([
            'company_id' => $user->company_id,
            'name' => $menu['name'],
            'color' => $menu['color'],
        ]);

        foreach ($menu['pages'] as $pageData) {
            $menuPage = $createdMenu->menuPages()->create([
                'count' => $pageData['count'],
            ]);

            foreach ($pageData['items'] as $itemData) {
                $menuItem = $menuPage->menuItems()->create([
                    'width' => $itemData['width'],
                    'height' => $itemData['height'],
                    'top' => $itemData['top'],
                    'left' => $itemData['left'],
                    'type' => $itemData['type'],
                ]);

                if ($itemData['type'] === 'merch') {
                    $menuItem->menuItemMerch()->create([
                        'merch_id' => $itemData['merchId'],
                    ]);
                }
                if ($itemData['type'] === 'text') {
                    $menuItem->menuItemTexts()->create([
                        'color' => $itemData['color'],
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'メニューが正常に追加されました！',
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $menu = $request["menu"];  

        $updatedMenu = Menu::find($id);
        $updatedMenu->update([
            'company_id' => $user->company_id,
            'name' => $menu['name'],
            'color' => $menu['color'],
        ]);

        $updatedMenu->menuPages()->delete();

        foreach ($menu['pages'] as $pageData) {
            $menuPage = $updatedMenu->menuPages()->create([
                'count' => $pageData['count'],
            ]);

            foreach ($pageData['items'] as $itemData) {
                $menuItem = $menuPage->menuItems()->create([
                    'width' => $itemData['width'],
                    'height' => $itemData['height'],
                    'top' => $itemData['top'],
                    'left' => $itemData['left'],
                    'type' => $itemData['type'],
                ]);

                if ($itemData['type'] === 'merch') {
                    $menuItem->menuItemMerch()->create([
                        'merch_id' => $itemData['merchId'],
                    ]);
                }
                if ($itemData['type'] === 'text') {
                    $menuItem->menuItemTexts()->create([
                        'color' => $itemData['color'],
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'メニューが正常に更新されました！',
        ]);
    }

    public function destrory(Request $request)
    {
        $merchIds = $request['ids'];
        Menu::whereIn("id", $merchIds)->delete();

        return response()->json([
            'success' => true,
            'message' => '正常に削除されました！',
        ]);
    }
}
