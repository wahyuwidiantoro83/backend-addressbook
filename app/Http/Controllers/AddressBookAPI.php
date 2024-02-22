<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Nette\Utils\Random;

class AddressBookAPI extends Controller
{
    //
    function getAddressData(Request $request){
        $queryData = $request->only(['query','type','sort']);

        $result = Contact::select(['id','name','phone','type','address','images','email','created_at','updated_at']);
        if (isset($queryData['type']) && $queryData['type'] != '') {
            $result->where('type','LIKE','%'.$queryData['type'].'%');
        } 
        if (isset($queryData['query'])) {
            $result->where(function ($query) use ($queryData) {
                $query->where('name', 'LIKE', '%' . $queryData['query'] . '%')
                    ->orWhere('phone', 'LIKE', '%' . $queryData['query'] . '%')
                    ->orWhere('email', 'LIKE', '%' . $queryData['query'] . '%');
            });
        } 
        if (isset($queryData['sort'])) {
            if ($queryData['sort'] == 'asc') {
                $result->orderBy('name', 'asc');
            } else if ($queryData['sort'] == 'desc') {
                $result->orderBy('name', 'desc');
            }
        }
        $finalResult = $result->get();
        return response()->json([
            'success' => true,
            'message' => 'Success get contacts data',
            'result' => $finalResult
        ],200);
    }

    function getTypeEnum(){
        return response()->json([
            'success' => true,
            'message' => 'Success get contacts data',
            'result' => Contact::getTypeEnum()
        ],200);
    }

    function getAddressDetail($id){
        $result = Contact::select(['id','name','phone','type','address','images','email','created_at','updated_at'])->where('id','=',$id)->where('deleted_at','=',NULL)->first();
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Contact found',
                'result' => $result
            ],200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No contact found',
                'result' => NULL
            ],404);
        }
    }

    function createAddressData(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:13',
            'type' => ['required', 'string', 'max:50', Rule::in(Contact::getTypeEnum())],
            'images' => 'file|mimes:jpg,jpeg,png,gif|max:1026',
            'address' => 'string|max:255',
            'email' => 'email|max:255',
        ]);
          
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Input',
                'result' => $validator->errors()
            ], 401);
        }

        $postData = $request->only(['name','phone','type','address','email']);

        $data = [
            "name" => $postData['name'],
            "phone" => $postData['phone'],
            "type" => $postData['type'],
            "address" => $postData['address'],
            "email" => $postData['email'],
        ];

        if ($request->file('images')) {
            $file = $request->file('images');
            $fileName = time() . '_' . Random::generate(10,'abcdefghijklmnopqrstuvwxyz') . '.' . $file->getClientOriginalExtension();
            $file->storeAs('avatar', $fileName, 'public');
            $data['images'] = $fileName;
        }

        $result = Contact::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Success create contact',
            'result' => $result
        ], 201);
    }

    function updateAddressData(Request $request, $id){
        $isExist = Contact::find($id);
        if (!$isExist) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found',
                'result' => NULL
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:13',
            'type' => ['required', 'string', 'max:50', Rule::in(Contact::getTypeEnum())],
            'images' => 'file|mimes:jpg,jpeg,png,gif|max:1026',
            'address' => 'string|max:255',
            'email' => 'email|max:255',
        ]);
          
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Input',
                'result' => $validator->errors()
            ], 401);
        }
        
        $postData = $request->only(['name','phone','type','address','email']);

        if ($request->post('delete_image')) {
            $postData['images'] = NULL;
            $oldFilePath = 'public/avatar/'.$isExist->images;
            if (Storage::exists($oldFilePath)) {
                Storage::delete($oldFilePath);
            }
        }

        if ($request->file('images')) {
            $file = $request->file('images');
            $fileName = time() . '_' . Random::generate(10,'abcdefghijklmnopqrstuvwxyz') . '.' . $file->getClientOriginalExtension();
            $file->storeAs('avatar', $fileName, 'public');
            $postData['images'] = $fileName;
            $oldFilePath = 'public/avatar/'.$isExist->images;
            if (Storage::exists($oldFilePath)) {
                Storage::delete($oldFilePath);
            }
        }

        $result = Contact::where('id',$isExist->id)->update($postData);

        return response()->json([
            'success' => true,
            'message' => 'Success update data',
            'result' => NULL
        ], 201);
    }

    function deleteAddressData($id){
        $result = Contact::find($id);
        if ($result) {
            $result->delete();
            return response()->json([
                'success' => true,
                'message' => 'Success delete contact',
                'result' => NULL
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed delete contact',
                'result' => NULL
            ], 404);
        }
    }

    function importJSONFile(Request $request){
        $validator = Validator::make($request->all(), [
            'addressJSON' => 'file|mimes:json|max:1026',
        ]);
          
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Input',
                'result' => $validator->errors()
            ], 401);
        }
        $JSONFile = $request->file('addressJSON'); 
        $fileData = json_decode(file_get_contents($JSONFile->getPathname()));
        foreach ($fileData as $key => $value) {
            $isExist = Contact::where('phone', $value->phone)->first();
            $data = [
                "name" => $value->name,
                "address" => $value->address,
                "type" => $value->type,
                "email" => $value->email
            ];
            if (!$isExist) {
                $data['phone'] = $value->phone;
                Contact::create($data);
            } else {
                Contact::where('phone', $value->phone)->update($data);
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Success import file',
            'result' => NULL
        ], 201);
    }
}
