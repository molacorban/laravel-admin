<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Routing\Controller;

class RoleController extends Controller
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
            ->header(trans('admin.roles'))
            ->description(trans('admin.list'))
            ->breadcrumb(
                ['text' => 'Papeis' , 'url' => 'auth/roles'],
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
            ->header(trans('admin.roles'))
            ->description(trans('admin.detail'))
            ->breadcrumb(
                ['text' => 'Papeis' , 'url' => 'auth/roles'],
                ['text' => 'Visualizando'],
                ['text' => $id]
            )
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header(trans('admin.roles'))
            ->description(trans('admin.edit'))
            ->breadcrumb(
                ['text' => 'Papeis' , 'url' => 'auth/roles'],
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
            ->header(trans('admin.roles'))
            ->description(trans('admin.create'))
            ->breadcrumb(
                ['text' => 'Papeis' , 'url' => 'auth/roles'],
                ['text' => 'Novo']
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
        $roleModel = config('admin.database.roles_model');

        $grid = new Grid(new $roleModel());

        $grid->id('ID')->sortable();
        $grid->slug(trans('admin.slug'));
        $grid->name(trans('admin.name'));

        $grid->permissions(trans('admin.permission'))->pluck('name')->label();

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

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->row->slug == 'administrator') {
                $actions->disableDelete();
            }
        });

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
        $roleModel = config('admin.database.roles_model');

        $show = new Show($roleModel::findOrFail($id));

        $show->id('ID');
        $show->slug(trans('admin.slug'));
        $show->name(trans('admin.name'));
        $show->permissions(trans('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
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
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $roleModel());

        $form->display('id', 'ID');

        $form->text('slug', trans('admin.slug'))->rules('required');
        $form->text('name', trans('admin.name'))->rules('required');
        $form->listbox('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        return $form;
    }
}
