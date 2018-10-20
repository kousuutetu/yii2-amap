<?php

namespace ginkgo\amap;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\web\HttpException;

class Yuntu extends \yii\base\Component
{
    public $key;
    public $tableid;

    public function init()
    {
        if ($this->key === null) {
            throw new InvalidConfigException('key 必填');
        }
        if ($this->tableid === null) {
            throw new InvalidConfigException('tableid 必填');
        }
    }

    public function create($name, $location, $options=[])
    {
        return $this->datamanage('create', ['data' => Json::encode(ArrayHelper::merge([
            '_name' => $name,
            '_location' => $location,
        ], $options))]);
    }

    public function update($id, $options=[])
    {
        return $this->datamanage('update', ['data' => Json::encode(ArrayHelper::merge([
            '_id' => $id,
        ], $options))]);
    }

    public function delete($ids)
    {
        $ids = is_array($ids) ? implode(',', $ids) : $ids;
        return $this->datamanage('delete', ['ids' => $ids]);
    }

    public function around($center, $options=[])
    {
        $data = ArrayHelper::merge([
            'center' => $center,
        ], $options);
        return $this->datasearch('around', $data);
    }

    public function datamanage($action, $data)
    {
        $data = ArrayHelper::merge([
            'key'  => $this->key,
            'tableid' => $this->tableid,
        ], $data);

        $client = new Client(['baseUrl' => 'http://yuntuapi.amap.com/datamanage/data/']);
        $response = $client->createRequest()
            ->setUrl($action)
            ->setMethod('post')
            ->addHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
            ->setData($data)
            ->send();

        if (!$response->isOk) {
            return false;
        }

        if (!$response->data['status']) {
            return false;
        }

        return $response->data;
    }

    public function datasearch($action, $data)
    {
        $data = ArrayHelper::merge([
            'key'  => $this->key,
            'tableid' => $this->tableid,
        ], $data);

        $client = new Client(['baseUrl' => 'https://yuntuapi.amap.com/datasearch/']);
        $response = $client->createRequest()
            ->setUrl($action)
            ->setMethod('get')
            ->setData($data)
            ->send();

        if (!$response->isOk) {
            return false;
        }

        if (!$response->data['status']) {
            return [];
        }

        return [
            'count' => $response->data['count'],
            'datas' => $response->data['datas'],
        ];
    }
}
