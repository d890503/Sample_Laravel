<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function __construct()
    {
        //我们通过 except 方法来设定 指定动作
        // 不使用 Auth 中间件进行过滤，
        //意为 —— 除了此处指定的动作以外，所有其他动作都必须登录用户才能访问，类似于黑名单的过滤机制
        $this->middleware('auth', [
            'except' =>  ['show', 'create', 'store', 'index']
        ]);

        //使用 Auth 中间件提供的 guest 选项，
        //用于指定一些只允许未登录用户访问的动作，
        //因此我们需要通过对 guest 属性进行设置，只让未登录用户访问登录页面和注册页面。
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }


    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }
    /**
     * @description 用户注册
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * @description 用户信息展示
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function store(Request $request)
    {
        //验证字段
       $this->validate($request, [
           'name' => 'required|max:50',
           'email' => 'required|email|unique:users|max:255',
           'password' => 'required|confirmed|min:6'
       ]);

       //保存字段
       $user = User::create([
           'name' => $request->name,
           'email' => $request->email,
           'password' => bcrypt($request->password),
       ]);
       //自动登录
       Auth::login($user);
       session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
       return redirect()->route('users.show', [$user]);
    }

    public  function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'required|confirmed|min:6'
        ]);

        $this->authorize('update', $user);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user->id);
    }

    public  function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }
}
