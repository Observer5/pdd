<?php

namespace EasyPdd\Goods;

use EasyPdd\Core\AbstractAPI;
use EasyPdd\Support\Collection;

/**
 * Class Order
 *
 * @package EasyExpress\Order
 *
 * @see https://open.pinduoduo.com/#/document
 */
class Client extends AbstractAPI
{
    /**
     * @return Collection
     */
    public function test()
    {
        return new Collection([1, 2, 3]);
    }

    //拼多多商家发布的商品需要关联在拼多多标准类目下，调用pdd.goods.authorization.cats接口获取到当前商家可发布类目树，然后根据一级类目去查询同步到本地的拼多多标准类目树。ps.拼多多的类目树可能发生更改，需要定期同步；不同商家即使一级类目相同，二、三级类目可能不同。

    /**
     * 获取当前授权商家可发布的商品类目信息
     *
     * @param string $token
     *
     * @return Collection
     */
    public function authorizationCats($token)
    {
        return $this->request('pdd.goods.authorization.cats', $token);
    }
    
    //上传图片请使用pdd.goods.image.upload接口。


    //创建物流运费模板请使用pdd.goods.logistics.template.create接口，如果需要获取所有的物流运费模板可用pdd.goods.logistics.template.get接口获取。如果需要查询单个物流运费模板可用pdd.one.express.cost.template接口获取。
    public function logisticsTemplates($token)
    {
        return $this->request('pdd.goods.logistics.template.get', $token);
    }

    //通过pdd.goods.spec.get接口获取对应要发布的商品所属叶子类目所需的规格id；然后根据获取到的生成规格属性id请使用pdd.goods.spec.id.get接口生成商家自定义规格；部分类目已切至新版属性发布，请使用pdd.goods.cat.template.get获取到的规格值发布。
    public function spec($token)
    {
        return $this->request('pdd.goods.spec.get', $token);
    }

    //商品发布前，通过pdd.goods.cat.template.get查询该类目的商品发布需要的属性，获取商品发布需要的模板-属性-属性值
    public function catTemplate($token)
    {
        return $this->request('pdd.goods.cat.template.get', $token);
    }
    
    /**
     * @return \EasyPdd\Support\Collection
     */
    public function list($token)
    {
        return $this->request('pdd.goods.list.get', $token);
    }


    //pdd.goods.cats.get
    //pdd.goods.spec.get
    //pdd.goods.spec.id.get


    //pdd.goods.add 3times/sec, 1200times/day

    //pdd.goods.cat.template.get


}