<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use App\Page;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

    public static $async = [
        'reorder' => [],
        'reorderPages' => []
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.main', ['title' => 'Главная страница', 'entity' => 'admin', 'data' => [], 'pure' => true]);
    }

    /**
     * Returns the sidebar list to the sidebar partial inside of any admin view.
     *
     * @return array
     */
    public static function sidebar()
    {
        return [
            'Главная' => '/admin',
            'split1' => '',
            'Новости' => '/admin/news',
            'ASCII' => '/admin/ascii',
            'split2' => '',
            'Страницы' => '/admin/pages',
            'Отображения' => '/admin/views',
            'Тексты' => '/admin/texts',
            'split3' => '',
            'Настройки' => '/admin/settings',
            'Резервные копии' => '/admin/backups',
        ];
    }

    /**
     * Returns the correct singular entity name by the plural variant.
     *
     * @param $name
     * @return mixed
     */
    public static function names($name)
    {
        $names = [
            'pages' => 'Page',
            'views' => 'View',
            'texts' => 'Text',
            'news' => 'NewsItem',
            'settings' => 'Setting',
            'backups' => 'Backup',
            'ascii' => 'Ascii'
        ];
        return $names[$name];
    }

    /**
     * Returns the view according to the $entity value - or the definite view in the case if $view is set.
     *
     * @param $entity
     * @param bool|false $view
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function entity($entity, $view = false)
    {
        $item = 'App\\' . self::names($entity);
        $data = $item::adminList()->get();
        $view = $view ? $view : $entity;
        return view('admin.list.' . $view, ['entity' => $entity, 'data' => $data, 'title' => array_search($entity, self::sidebar())]);
    }

    /**
     * Returns the creating view with the empty model attached.
     *
     * @param $entity
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($entity)
    {
        $class = 'App\\' . self::names($entity);
        $item = new $class;
        $action = property_exists($item, 'controller') ? $item->controller . '@store' : (ucfirst($entity) . 'Controller@store');
        return view('admin.edit.' . $entity, [
            'item' => $item,
            'type' => 'create',
            'entity' => $entity,
            'action' => $action,
            'method' => 'POST',
            'images' => $this->fetchAttachments('images', $class, $item),
            'files' => $this->fetchAttachments('files', $class, $item),
        ]);
    }

    /**
     * Returns the editing view with the fetched by id model attached.
     *
     * @param $entity
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($entity, $id)
    {
        $class = 'App\\' . self::names($entity);
        $item = $class::findOrFail($id);
        $action = property_exists($item, 'controller') ? $item->controller . '@update' : (ucfirst($entity) . 'Controller@update');
        return view('admin.edit.' . $entity, [
            'item' => $item,
            'type' => 'edit',
            'entity' => $entity,
            'action' => [$action, $item->id],
            'method' => 'PUT',
            'images' => $this->fetchAttachments('images', $class, $item),
            'files' => $this->fetchAttachments('files', $class, $item),
        ]);
    }

    /**
     * Nice and common moving method. Called from the list-type views with the arrows up and down.
     *
     * @param $redirect
     * @param $id
     * @param $direction
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function move($redirect, $id, $direction)
    {
        $entity = 'App\\' . self::names($redirect);
        $items = $entity::all();
        $item = $entity::findOrFail($id);
        $existing = $entity::where('pos', $direction == 'up' ? $item->pos - 1 : $item->pos + 1)->first();
        if ($existing) {
            if (($direction == 'up' ? $item->pos > 1 : $item->pos < sizeof($items))) {
                $item->pos = $direction == 'up' ? $item->pos - 1 : $item->pos + 1;
                $item->save();
                $existing->pos = $direction == 'up' ? $existing->pos + 1 : $existing->pos - 1;
                $existing->save();
            }
        }
        return redirect('/admin/' . $redirect);
    }

    public static function reorder($json_data)
    {
        $validation = validate_true_with_message([
            [
                'check' => isset($json_data['entity']),
                'message' => 'Не передана сущность!'
            ],
            [
                'check' => $json_data['entity'] !== 'false',
                'message' => 'Сущность не тру'
            ],
            [
                'check' => isset($json_data['order']),
                'message' => 'Не передан ордер!'
            ],
        ]);
        if ($validation !== true) return error($validation);
        if (!Auth::user()->admin) return error('Доступ запрещен');
        $items = $json_data['entity']::whereIn('id', $json_data['order'])->get();
        if (!sizeof($items)) return error('Пустой сортируемый список!');
        foreach ($items as $index => &$item) {
            $item['pos'] = array_search($item->id, $json_data['order']);
            $item->save();
        }
        $entity_name = strtolower($items->first()->getShortClass());
        ah(view('admin.iteration.' . $entity_name, ['items' => $items->sortBy('pos')])->render());
        return true;
    }

    /**
     * Scans the model for the images() method which shows the list of attached image entities.
     *
     * @param $type
     * @param $class
     * @param $item
     * @return array
     */
    public function fetchAttachments($type, $class, $item)
    {
        $images = [];
        if (method_exists($class, $type)) {
            foreach ($class::$type() as $name => $entityText) {
                $entity = 'App\\' . $entityText;
                $images[] = [
                    'name' => $name,
                    'list' => $entity::attachmentTo($item->id)->get(),
                    'entity' => $entityText
                ];
            }
        }
        return $images;
    }
}
