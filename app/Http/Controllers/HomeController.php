<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\PostComment;
use App\Rules\MatchOldPassword;
use Hash;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(){
        return view('user.index');
    }

    public function profile(){
        $profile=Auth()->user();
        return view('user.users.profile')->with('profile',$profile);
    }

    public function profileUpdate(Request $request,$id){
        $user=User::findOrFail($id);
        $data=$request->all();
        $status=$user->fill($data)->save();
        if($status){
            request()->session()->flash('success','Successfully updated your profile');
        }
        else{
            request()->session()->flash('error','Please try again!');
        }
        return redirect()->back();
    }

    // Order
    public function orderIndex(){
        $orders=Order::orderBy('id','DESC')->where('user_id',auth()->user()->id)->paginate(10);
        return view('user.order.index')->with('orders',$orders);
    }

    public function userOrderDelete($id)
    {
        $order = Order::where('user_id', auth()->user()->id)->where('id', $id)->first();

        if($order){
            // Si la orden ya está en proceso, entregada o cancelada, no se modifica
            if($order->status == "process" || $order->status == 'delivered' || $order->status == 'cancel'){
                return redirect()->back()->with('error', 'No puedes cancelar este pedido porque ya está en proceso o entregado.');
            }
            else{
                // En lugar de borrar la orden, cambiamos su estado a 'cancel'
                $order->status = 'cancel';
                $status = $order->save();

                if($status){
                    request()->session()->flash('success', 'Pedido cancelado correctamente');
                }
                else{
                    request()->session()->flash('error', 'No se pudo cancelar el pedido');
                }
                return redirect()->route('user.order.index');
            }
        }
        else{
            request()->session()->flash('error', 'Pedido no encontrado');
            return redirect()->back();
        }
    }

    public function orderShow($id)
    {
        $order=Order::find($id);
        return view('user.order.show')->with('order',$order);
    }

    // Product Review
    public function productReviewIndex(){
        $reviews=ProductReview::getAllUserReview();
        return view('user.review.index')->with('reviews',$reviews);
    }

    public function productReviewEdit($id)
    {
        $review=ProductReview::find($id);
        return view('user.review.edit')->with('review',$review);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function productReviewUpdate(Request $request, $id)
    {
        $review=ProductReview::find($id);
        if($review){
            $data=$request->all();
            $status=$review->fill($data)->update();
            if($status){
                request()->session()->flash('success','Review Successfully updated');
            }
            else{
                request()->session()->flash('error','Something went wrong! Please try again!!');
            }
        }
        else{
            request()->session()->flash('error','Review not found!!');
        }

        return redirect()->route('user.productreview.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function productReviewDelete($id)
    {
        $review=ProductReview::find($id);
        $status=$review->delete();
        if($status){
            request()->session()->flash('success','Successfully deleted review');
        }
        else{
            request()->session()->flash('error','Something went wrong! Try again');
        }
        return redirect()->route('user.productreview.index');
    }

    public function userComment()
    {
        $comments=PostComment::getAllUserComments();
        return view('user.comment.index')->with('comments',$comments);
    }

    public function userCommentDelete($id)
    {
        // Bloqueo de seguridad: los usuarios no pueden eliminar sus comentarios para proteger la integridad de las publicaciones
        request()->session()->flash('error', 'Los comentarios publicados no pueden ser eliminados por el usuario.');
        return redirect()->back();
    }

    public function userCommentEdit($id)
    {
        // Validación de propiedad para la vista de detalle
        $comments = PostComment::where('user_id', auth()->user()->id)->where('id', $id)->first();
        if($comments){
            return view('user.comment.edit')->with('comment',$comments);
        }
        else{
            request()->session()->flash('error','Comment not found');
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function userCommentUpdate(Request $request, $id)
    {
        // CONTROL DE SEGURIDAD:
        // Los usuarios normales no deben tener autorización para editar comentarios ni cambiar el estado de moderación.
        request()->session()->flash('error', 'No tienes permisos para modificar los comentarios enviados.');
        return redirect()->route('user.post-comment.index');
    }

    public function changePassword(){
        return view('user.layouts.userPasswordChange');
    }

    public function changPasswordStore(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);
   
        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);
   
        return redirect()->route('user')->with('success','Password successfully changed');
    }
}