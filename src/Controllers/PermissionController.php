<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header(trans('admin.permissions'))
            ->description(trans('admin.list'))
            ->breadcrumb(
                ['text' => 'Permissões' , 'url' => 'auth/permissions'],
                ['text' => 'Listando']
            )
            ->body($this->grid()->render());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header(trans('admin.permissions'))
            ->description(trans('admin.detail'))
            ->breadcrumb(
                ['text' => 'Permissões' , 'url' => 'auth/permissions'],
                ['text' => 'Visualizar'],
                ['text' => $id]
            )
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header(trans('admin.permissions'))
            ->description(trans('admin.edit'))
            ->breadcrumb(
                ['text' => 'Permissões' , 'url' => 'auth/permissions'],
                ['text' => 'Editando'],
                ['text' => $id]
            )
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header(trans('admin.permissions'))
            ->description(trans('admin.create'))
            ->breadcrumb(
                ['text' => 'Permissões' , 'url' => 'auth/permissions'],
                ['text' => 'Nova']
            )
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $permissionModel = config('admin.database.permissions_model');

        $grid = new Grid(new $permissionModel());

        $grid->id('ID')->sortable();
        $grid->slug(trans('admin.slug'));
        $grid->name(trans('admin.name'));

        $grid->http_path(trans('admin.route'))->display(function ($path) {
            return collect(explode("\r\n", $path))->map(function ($path) {
                $method = $this->http_method ?: ['ANY'];

                if (Str::contains($path, ':')) {
                    list($method, $path) = explode(':', $path);
                    $method = explode(',', $method);
                }

                $method = collect($method)->map(function ($name) {
                    return strtoupper($name);
                })->map(function ($name) {
                    return "<span class='label label-primary'>{$name}</span>";
                })->implode('&nbsp;');

                if (!empty(config('admin.route.prefix'))) {
                    $path = '/'.trim(config('admin.route.prefix'), '/').$path;
                }

                return "<div style='margin-bottom: 5px;'>$method<code>$path</code></div>";
            })->implode('');
        });

        $grid->column('created_at', trans('admin.created_at'))->display(function () {
            if(!empty($this->created_at)){
                return $this->created_at->format('d/m/Y H:i:s');
            }
        })->sortable();

        $grid->column('updated_at', trans('admin.updated_at'))->display(function () {
            if(!empty($this->updated_at)){
                return $this->updated_at->format('d/m/Y H:i:s');
            }
        })->sortable();

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $permissionModel = config('admin.database.permissions_model');

        $show = new Show($permissionModel::findOrFail($id));

        $show->id('ID');
        $show->slug(trans('admin.slug'));
        $show->name(trans('admin.name'));

        $show->http_path(trans('admin.route'))->as(function ($path) {
            return collect(explode("\r\n", $path))->map(function ($path) {
                $method = $this->http_method ?: ['ANY'];

                if (Str::contains($path, ':')) {
                    list($method, $path) = explode(':', $path);
                    $method = explode(',', $method);
                }

                $method = collect($method)->map(function ($name) {
                    return strtoupper($name);
                })->map(function ($name) {
                    return "<span class='label label-primary'>{$name}</span>";
                })->implode('&nbsp;');

                if (!empty(config('admin.route.prefix'))) {
                    $path = '/'.trim(config('admin.route.prefix'), '/').$path;
                }

                return "<div style='margin-bottom: 5px;'>$method<code>$path</code></div>";
            })->implode('');
        });

        $show->created_at()->as(function ($created_at) {
            if(!empty($created_at)) {
                return $created_at->format('d/m/Y H:i:s');
            }
        });
        $show->updated_at()->as(function ($updated_at) {
            if(!empty($updated_at)) {
                return $updated_at->format('d/m/Y H:i:s');
            }
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $permissionModel = config('admin.database.permissions_model');

        $form = new Form(new $permissionModel());

        $form->display('id', 'ID');

        $form->text('slug', trans('admin.slug'))->rules('required');
        $form->text('name', trans('admin.name'))->rules('required');

        $form->multipleSelect('http_method', trans('admin.http.method'))
            ->options($this->getHttpMethodsOptions())
            ->help(trans('admin.all_methods_if_empty'));
        $form->textarea('http_path', trans('admin.http.path'));

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        return $form;
    }

    /**
     * Get options of HTTP methods select field.
     *
     * @return array
     */
    protected function getHttpMethodsOptions()
    {
        $permissionModel = config('admin.database.permissions_model');

        return array_combine($permissionModel::$httpMethods, $permissionModel::$httpMethods);
    }
}
