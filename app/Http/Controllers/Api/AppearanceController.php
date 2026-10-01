<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppearanceController extends Controller
{
    private const SLOTS = [
        'SMARTLOG_LOGO' => ['title'=>'SmartLog Official Logo','description'=>'Official SmartLog logo','base'=>'smartlog-logo','default'=>'images/smartlog/smartlog-logo.png'],
        'PUBLIC_HERO' => ['title'=>'Public Landing Page','description'=>'Public landing page image','base'=>'public-hero','default'=>'images/smartlog/clinical-team.jpg'],
        'LECTURER_HERO' => ['title'=>'Lecturer Dashboard','description'=>'Lecturer portal banner','base'=>'lecturer-hero','default'=>'images/smartlog/clinical-team.jpg'],
        'HOD_HERO' => ['title'=>'HOD Dashboard','description'=>'HOD portal banner','base'=>'hod-hero','default'=>'images/smartlog/campus.jpg'],
        'ADMIN_HERO' => ['title'=>'ICT Admin Dashboard','description'=>'ICT Admin portal banner','base'=>'admin-hero','default'=>'images/smartlog/campus.jpg'],
    ];

    private function directory(): string { return public_path('images/smartlog/custom'); }
    private function configFor(string $slot): array { abort_unless(isset(self::SLOTS[$slot]),404); return self::SLOTS[$slot]; }
    private function currentFile(array $slot): ?string
    {
        $dir=$this->directory();
        $pointer=$dir.DIRECTORY_SEPARATOR.$slot['base'].'.txt';
        if(is_file($pointer)) {
            $filename=trim(file_get_contents($pointer));
            if(preg_match('/^'.preg_quote($slot['base'],'/').'\.(jpg|jpeg|png|webp)$/i',$filename) && is_file($dir.DIRECTORY_SEPARATOR.$filename)) return $filename;
        }
        foreach(['jpg','jpeg','png','webp'] as $ext) {
            $name=$slot['base'].'.'.$ext;
            if(is_file($dir.DIRECTORY_SEPARATOR.$name)) return $name;
        }
        return null;
    }
    public function index()
    {
        $slots=[];
        foreach(self::SLOTS as $key=>$slot) {
            $file=$this->currentFile($slot);
            $slots[]=['key'=>$key,'title'=>$slot['title'],'description'=>$slot['description'],
                'custom_exists'=>$file!==null,'url'=>$file!==null
                ? asset('images/smartlog/custom/'.$file).'?v='.filemtime($this->directory().DIRECTORY_SEPARATOR.$file)
                : asset($slot['default'])];
        }
        return response()->json(['slots'=>$slots]);
    }
    public function upload(Request $request,string $slot)
    {
        $config=$this->configFor($slot);
        $request->validate(['image'=>['required','image','mimes:jpg,jpeg,png,webp','max:8192']]);
        $dir=$this->directory();
        if(!is_dir($dir) && !mkdir($dir,0755,true) && !is_dir($dir)) abort(500,'Unable to create image directory');
        $file=$request->file('image');
        $ext=strtolower($file->extension());
        if($ext==='jpeg') $ext='jpg';
        abort_unless(in_array($ext,['jpg','png','webp'],true),422,'Unsupported image format');
        $filename=$config['base'].'.'.$ext;
        // Move new file first, then remove old extensions.
        $file->move($dir,$filename);
        foreach(['jpg','jpeg','png','webp'] as $oldExt) {
            $old=$config['base'].'.'.$oldExt;
            if($old!==$filename && is_file($dir.DIRECTORY_SEPARATOR.$old)) unlink($dir.DIRECTORY_SEPARATOR.$old);
        }
        file_put_contents($dir.DIRECTORY_SEPARATOR.$config['base'].'.txt',$filename,LOCK_EX);
        return response()->json(['message'=>'Image updated successfully.']);
    }
    public function reset(string $slot)
    {
        $config=$this->configFor($slot);
        $dir=$this->directory();
        foreach(['jpg','jpeg','png','webp','txt'] as $ext) {
            $path=$dir.DIRECTORY_SEPARATOR.$config['base'].'.'.$ext;
            if(is_file($path)) unlink($path);
        }
        return response()->json(['message'=>'Image restored to default.']);
    }
}