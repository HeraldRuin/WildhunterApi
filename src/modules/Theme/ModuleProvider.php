<?php
namespace Modules\Theme;

use Themes\Base\ThemeProvider;

class ModuleProvider extends \Modules\ModuleServiceProvider
{


    public function register(): void
    {
        $this->app->register(RouterServiceProvider::class);
    }
    public static function getAdminMenu(): array
    {
        return [
            'theme'=>[
                'title'=>__("Themes"),
                'url'=>route("theme.admin.index"),
                "permission"=>"theme_manage",
                "position"=>70,
                'icon'=>"fa fa-paint-brush",
                "group"=>"system",
            ]
        ];
    }
}
