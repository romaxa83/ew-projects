<?php

namespace Tests\Feature\Http\Api\V1\Common\FileBrowser;

use App\Services\FileBrowser\Actions\Permissions;

/**
 * @group FileBrowser
 */
class PermissionsTest extends AbstractFileBrowserTest
{
    public function test_it_browse_permissions_success(): void
    {
        $superadmin = $this->loginUserAsSuperAdmin();
        $prefix = $superadmin->getFileBrowserPrefix();
        $this->fileBrowser->setFileBrowserPrefix($prefix);

        $this->postJson(route('api.v1.filebrowser.browse'), [
            'action' => Permissions::ACTION,
            'source' => 'default',
        ])
            ->assertOk()
            ->assertJson(
                [
                    'success' => true,
                    'code' => 220,
                    'data' => [
                        'permissions' => [
                        ],
                    ],
                ]
            );
    }

}
