# laravel-sdk-mts

#### 项目介绍

阿里云SDK媒体转码管理包

## install

this package  for laravel

```
composer require siaoynli/laravel-alicloud-mts
```
add the   
```
Siaoynli\AliCloud\Mts\LaravelAliMtsServerProvider::class   
```
to the providers array in config/app.php

```
php artisan vendor:publish --provider="Siaoynli\AliCloud\Mts\LaravelAliMtsServerProvider"
```


## alias

```
 "Mts" => \Siaoynli\AliCloud\Mts\Facades\Mts::class,
```

## 使用方法

```
 use Mts;
 
 //不打水印
 $response=Mts::input("11.mp4","hzwwp")->output("a.mp4","69645e9dda5341d9a3ec5a5d82f817cb","hzwwp")->getAcsResponse();
 
 //打图片水印
 
 $image_watermark = array(
            "template_id"=>'45b67a2f23e04e345dfg11b3ed7a77f', //水印模板
           'type' => 'Image',
            'pos' => 'TopRight',
            'width' => 300, //水印宽度
           'dx' => 10, //x间距
            'dy'=> 10 //y间距
  );
 
  $response=Mts::input("11.mp4","hzwwp")->setImgWater("flu.png",$image_watermark,"hzwwp")->output("a.mp4","69645e9dda5341d9a3ec5a5d82f817cb","hzwwp")->getAcsResponse();
  
  //获取转码状态
  $job_id="00ee7714d880493e868656af5f645e72";
  $result=Mts::getAcsJobStatus(job_id)
  
    输出

array:2 [▼
  "state" => 1
  "data" => array:4 [▼
    "job_id" => "81146b108f1e43149f6c743bf270e527"
    "state" => "TranscodeSuccess"
    "percent" => 100
    "video_length" => "228"
  ]
]
  
  //视频截图
  
   $response=Mts::snapshot("11.mp4","aa.jpg","hzwwp")->getSnapshotResponse();
   
   //输出
   "state" => 1
      "data" => array:2 [▼
        "job_id" => "abf325e69053424eaa03c587d822b8d3"
        "file_lists" => array:5 [▼
          0 => "aa_00001.jpg"
          1 => "aa_00002.jpg"
          2 => "aa_00003.jpg"
          3 => "aa_00004.jpg"
          4 => "aa_00005.jpg"
        ]
  ]
  //获取状态
  $job_id="abf325e69053424eaa03c587d822b8d3"
  $result=Mts::getSnapshotJobStatus(job_id)
```

## 说明

```
todo  视频文字水印
```

## 
