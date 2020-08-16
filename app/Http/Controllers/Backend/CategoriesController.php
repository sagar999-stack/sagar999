<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Image;
use File;


class CategoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    public function index()

    {
    	$categories = Category::orderby('id','desc')->get();
    	return view('backend.pages.categories.index')->with('categories',$categories);
    }

    public function create()


    {
    	$main_categories = Category::orderby('name','desc')->where('parent_id',NULL)->get();
    	return view('backend.pages.categories.create',compact('main_categories'));
    }
    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required',
            'image'=>'Nullable|image ',
        ],

        [
            'name.required' => 'Please provide a category name',
            'image.image' => 'Please provide a valid image with .jpg, .png, .gif, .jpeg extention',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->description =$request->description;
        $category->parent_id =$request->parent_id;



         if (($request->image) >0){
        
                 
           $image = $request->file('image');
            $img = time().'.'.$image->getClientOriginalExtension();
            $location = public_path('images/categories/'.$img);
            Image::make($image)->save($location);
            $category->image = $img;
        }

        $category->save();

        session()->flash('success', 'A new categories has added successfully');

        return redirect()->route('admin.categories');
    }

    public function edit($id)
    {
        $main_categories = Category::orderby('name','desc')->where('parent_id',NULL)->get();
        $category= Category::find($id);
        if(!is_null($category)){
            return view('backend.pages.categories.edit', compact('category','main_categories'));
        }
        else {
            return redirect()->route('admin.categories');
        }
    }



    //update function

      public function update(Request $request,$id)
    {
       
        $this->validate($request,[
            'name' => 'required',
            'image'=>'Nullable|image ',

        ],

        [
            'name.required' => 'Please provide a category name',
            'image.image' => 'Please provide a valid image with .jpg, .png, .gif, .jpeg extention',
        ]);
  
        $category =  Category::find($id);
         $category->name = $request->name;
         $category->description =$request->description;
       $category->parent_id =$request->parent_id;



         if (($request->image) >0){
        // // delete old image
            
           if(File::exists('images/categories/'.$category->image))
            {
              File::delete('images/categories/'.$category->image) ;
            }   
          
           $image2 = $request->file('image');
           $img = time().'.'.$image2->getClientOriginalExtension();
            $location = public_path('images/categories/'.$img);
            Image::make($image2)->save($location);
            $category->image = $img;
       }

        $category->save();

        session()->flash('success', 'Updated  successfully');

        return redirect()->route('admin.categories');
    }

       public function delete($id){
        $category = Category::find($id);
    
        if (!is_null($category)) {

            // if it is parent category , then dekete the sub category
            if ($category->parent_id == NULL) {
               $sub_categories = Category::orderBy('name','desc')->where('parent_id',$category->id)->get();

               foreach ($sub_categories as $sub) {
                   $sub->delete();
                   if(File::exists('images/categories/'.$sub->image))
            {
              File::delete('images/categories/'.$sub->image) ;
            }
               }
            }
            // Delete category image
            if(File::exists('images/categories/'.$category->image))
            {
              File::delete('images/categories/'.$category->image) ;
            } 
            $category->delete();
        }

        session()->flash('success','Product has been deleted successfully');
        return back();
    
    }
}
