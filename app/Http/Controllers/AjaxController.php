<?php

namespace App\Http\Controllers;

use App\Facades\Ajax;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class AjaxController extends Controller
{
    public static $interfaces = [
        'ajax' => [
            'controller' => 'App\Http\Controllers\AjaxController'
        ],
        'admin' => [
            'controller' => 'App\Http\Controllers\AdminController'
        ],
        'pages' => [
            'controller' => 'App\Http\Controllers\PagesController'
        ],
        'ascii' => [
            'controller' => 'App\Http\Controllers\AsciiController'
        ]
    ];

    public static $async = [
        'test' => [
            'conditions' => []
        ]
    ];

    public static $data = [];
    public static $errors = [];

    /**
     * Executes the ajax-assigned method in the requested controller.
     *
     * @param $route
     * @param $method
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function run($route, $method, Request $request)
    {
        if (!isset(self::$interfaces[$route])) return self::deny();
        $controller = self::$interfaces[$route]['controller'];
        if (method_exists($controller, $method) && isset($controller::$async) && isset($controller::$async[$method])) {
            //TODO: implement the `conditions` handling
            return self::answer($controller::$method(Input::all(), $request), $request);
        } else {
            return self::deny();
        }
    }

    /**
     * An example ajax-callable function. The algorithm to allow the ajax access to the function:
     * 1. Create the public static function
     * 2. Add its entry to the $async of the controller
     * 3. Check if the controller has its alias in the $interfaces of the AjaxController
     *
     * You can set all the output data to the Ajax::$data array. The same for the Ajax:$errors. The bool val the function
     * returns is the decision maker on what callback type would be called.
     *
     * KEEP IN MIND: the function should return either an array of single element or an array of data and HTTP code of the response.
     * The default HTTP code is 200.
     *
     * @param $args
     * @return bool
     */
    public static function test($args)
    {
        Ajax::$data['test'] = true;
        Ajax::$data['works'] = 'yes';
        Ajax::$data['args'] = $args;
        Ajax::$errors['err'] = 'yeah';
        return 'asd';
    }

    /**
     * The function to give the denial of the response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function deny()
    {
        return response()->json([
            'success' => false,
            'message' => 'no controller'
        ], 404);
    }

    /**
     * The function to give the correct AJAX answer.
     * Accepts one or two arguments got from the func_get_args and replacing the
     *
     * @param $ok
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     * @throws InternalErrorException
     */
    public static function answer($ok, $request)
    {
        if (!is_bool($ok)) throw new InternalErrorException('The given status variable is not a boolean');
        $result = [
            'status' => $ok,
            'data' => Ajax::$data,
            'errors' => Ajax::$errors,
            'debug' => env('APP_DEBUG'),
            'debug_messages' => Ajax::$debug
        ];
        if ($request->header('to') != '' && isset(Ajax::$html) && Ajax::$html != '') $result['html'] = Ajax::$html;
        return response()->json($result)->header('123', 'error');
    }
}
