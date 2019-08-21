<?php

namespace Siaoynli\AliCloud\Mts;
/*
* @Author: hzwlxy
* @Email: 120235331@qq.com
* @Github: http：//www.github.com/siaoynli
* @Date: 2019/8/20 14:03
* @Version:
* @Description:
*/

use AliCloud\Core\Exception\ClientException;
use AliCloud\Core\Exception\ServerException;
use AliCloud\Core\Profile\DefaultProfile;
use AliCloud\Core\DefaultAcsClient;
use AliCloud\Mts\QueryJobListRequest;
use AliCloud\Mts\SubmitJobsRequest;
use AliCloud\Mts\SubmitSnapshotJobRequest;
use Illuminate\Config\Repository;


class Mts
{

    protected $config;
    protected $client;
    protected $request;
    protected $output = array();

    public function __construct(Repository $config)
    {
        $this->config = $config->get("alimts");
        $access_key_id = $this->config['access_key_id'];
        $access_key_secret = $this->config['access_key_secret'];
        $region_id = $this->config['region_id'] ?: "cn-hangzhou";
        $profile = DefaultProfile::getProfile($region_id, $access_key_id, $access_key_secret);
        $this->client = new DefaultAcsClient($profile);

    }

    public function input($oss_input_object, $oss_bucket = null, $oss_location = null)
    {
        $this->request = new SubmitJobsRequest();
        $this->request->setAcceptFormat('JSON');
        $oss_location = $oss_location ?: $this->config['oss_location'];
        $oss_bucket = $oss_bucket ?: $this->config['oss_bucket'];

        $input = array('Location' => $oss_location,
            'Bucket' => $oss_bucket,
            'Object' => urlencode($oss_input_object));
        $this->request->setInput(json_encode($input));
        return $this;
    }


    public function setImgWater($image_watermark_object, $watermark_config, $oss_bucket = null, $oss_location = null)
    {
        $oss_location = $oss_location ?: $this->config['oss_location'];
        $oss_bucket = $oss_bucket ?: $this->config['oss_bucket'];
        $image_watermark_input = array(
            'Location' => $oss_location,
            'Bucket' => $oss_bucket,
            'Object' => urlencode($image_watermark_object)
        );


        $image_watermark = array(
            'WaterMarkTemplateId' => $watermark_config['template_id'],
            'Type' => $watermark_config['type'],
            'InputFile' => $image_watermark_input,
            'ReferPos' => $watermark_config['pos'],
            'Width' => $watermark_config['width'],
            'Dx' => $watermark_config['dx'],
            'Dy' => $watermark_config['dy']
        );

        $watermarks = array($image_watermark);
        $this->output['WaterMarks'] = $watermarks;
        return $this;
    }

    public function output($oss_output_object, $template_id, $oss_bucket = null, $oss_location = null)
    {
        $this->output['OutputObject'] = urlencode($oss_output_object);

        $this->output['TemplateId'] = $template_id;

        $oss_location = $oss_location ?: $this->config['oss_location'];
        $oss_bucket = $oss_bucket ?: $this->config['oss_bucket'];

        $outputs = array($this->output);
        $this->request->setOUtputs(json_encode($outputs));
        $this->request->setOutputBucket($oss_bucket);
        $this->request->setOutputLocation($oss_location);


        $this->request->setPipelineId($this->config['pipeline_id']);

        return $this;
    }

    public function done()
    {
        try {
            $response = $this->client->getAcsResponse($this->request);
            $data["job_id"] = $response->{'JobResultList'}->{'JobResult'}[0]->{'Job'}->{'JobId'};
            $data["state"] = $response->{'JobResultList'}->{'JobResult'}[0]->{'Job'}->{'State'};
            return ["state" => 1, "data" => $data];
        } catch (ServerException $e) {
            return ["state" => 0, "msg" => $e->getMessage()];
        } catch (ClientException $e) {
            return ["state" => 0, "msg" => $e->getMessage()];
        }
    }


    public function getJobStatus($job_id)
    {
        $this->request = new QueryJobListRequest();
        $this->request->setAcceptFormat('JSON');
        $this->request->setJobIds($job_id);
        try {
            $response = $this->client->getAcsResponse($this->request);
            $jobInfo = [
                'jobId' => $response->{'JobList'}->{'Job'}[0]->{'JobId'},
                'status' => $response->{'JobList'}->{'Job'}[0]->{'State'},
                'percent' => $response->{'JobList'}->{'Job'}[0]->{'Percent'},
            ];

            if ($jobInfo['status'] === 'TranscodeSuccess') {
                $jobInfo['video_length'] = $response->{'JobList'}->{'Job'}[0]->{'Output'}->{'Properties'}->{'Duration'};
            }
            return ["state" => 1, "data" => $jobInfo];

        } catch (ServerException $e) {
            return ["state" => 0, "msg" => $e->getMessage()];

        } catch (ClientException $e) {
            return ["state" => 0, "msg" => $e->getMessage()];
        }
    }


    public function snapshot($oss_input_object,$oss_output_object,$oss_bucket=null,$snapshot_config=[],$oss_location=null)
    {
        $this->request = new SubmitSnapshotJobRequest();
        $this->request->setAcceptFormat('JSON');
        $oss_location = $oss_location ?: $this->config['oss_location'];
        $oss_bucket = $oss_bucket ?: $this->config['oss_bucket'];

        $input = array('Location' => $oss_location,
            'Bucket' => $oss_bucket,
            'Object' => urlencode($oss_input_object));
        $this->request->setInput(json_encode($input));
        //处理输出图片名
        $temp=explode('.',$oss_output_object);
        $filename=$temp[0];

        $oss_output_object=$filename.'_{Count}.jpg';

        $output = array('Location' => $oss_location,
            'Bucket' => $oss_bucket,
            'Object' => urlencode($oss_output_object));
        $snapshot_config = array('OutputFile' => $output);

        $snapshot_config['Time'] = isset($snapshot_config['time'])?$snapshot_config['time']:$this->config["snapshot"]['time'];
        $snapshot_config['Interval'] = isset($snapshot_config['interval'])?$snapshot_config['interval']:$this->config["snapshot"]['interval'];
        $snapshot_config['Num'] = isset($snapshot_config['num'])?$snapshot_config['num']:$this->config["snapshot"]['num'];
        $snapshot_config['Height'] = isset($snapshot_config['height'])?$snapshot_config['height']:$this->config["snapshot"]['height'];
        $this->request->setSnapshotConfig(json_encode($snapshot_config));
        $this->request->setPipelineId($this->config['pipeline_id']);
        try {
            $response = $this->client->getAcsResponse($this->request);
             $file_list=[];
             $i=1;
             $temp_num = 100000;
             while ($i<=$snapshot_config['Num']){
                 $file_list[]=$filename.'_'.substr(($temp_num+$i),1,5).'.jpg';
                 $i++;
             }
            $jobInfo = [
                'jobId' =>  $response->{'SnapshotJob'}->{'Id'},
                'file_list' =>$file_list,
            ];
            return ["state" => 1, "data" => $jobInfo];
        } catch(ServerException $e) {
            return ["state" => 0, "msg" => $e->getMessage()];
        } catch(ClientException $e) {
            return ["state" => 0, "msg" => $e->getMessage()];
        }

    }


}
