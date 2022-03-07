<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // $roleId = auth()->user()->role_id;
        // $uri = $request->path();
        // $newUri = explode("/", $uri);
        // $urlRequest = $newUri[1];
        // $access_page = Role::where('role_id', $roleId)->first();
        // $roleAccessPage = json_decode($access_page->access_pages, true);
        // if (in_array($urlRequest, $roleAccessPage)) {
        //     return $next($request);
        // }
        // return response()->json(['status' => 'error', 'message' => 'You are not allowed to access this page']);
    }
}
