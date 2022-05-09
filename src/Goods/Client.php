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
    //拼多多商家发布的商品需要关联在拼多多标准类目下，调用pdd.goods.authorization.cats接口获取到当前商家可发布类目树，然后根据一级类目去查询同步到本地的拼多多标准类目树。ps.拼多多的类目树可能发生更改，需要定期同步；不同商家即使一级类目相同，二、三级类目可能不同。

    /**
     * 获取当前授权商家可发布的商品类目信息
     *
     * @param string $token
     * @param int $parentCatID  默认值=0，值=0时为顶点cat_id,通过树顶级节点获取一级类目
     *
     * @return Collection
     */
    public function authorizationCats($token, $parentCatID = 0)
    {
        return $this->request('pdd.goods.authorization.cats', $token, ['parent_cat_id' => $parentCatID]);
    }

    /**
     * 类目预测
     *
     * @param $token
     * @param $outerCatId
     * @param $outerCatName
     * @param $outerGoodsName
     *
     * @return Collection
     */
    public function outerCats($token, $outerCatId, $outerCatName, $outerGoodsName)
    {
        return $this->request('pdd.goods.outer.cat.mapping.get', $token, ['outer_cat_id' => $outerCatId, 'outer_cat_name' => $outerCatName, 'outer_goods_name' => $outerGoodsName]);
    }
    
    /**
     *
     * 上传图片
     *
     * @param $token
     * @param $image
     *
     * @return Collection
     */
    public function imageUpload($token, $image)
    {
        return $this->request('pdd.goods.image.upload', $token, ['image' => $image]);
    }

    //创建物流运费模板请使用pdd.goods.logistics.template.create接口，如果需要获取所有的物流运费模板可用pdd.goods.logistics.template.get接口获取。如果需要查询单个物流运费模板可用pdd.one.express.cost.template接口获取。

    /**
     *
     * 获取所有的物流运费模板
     *
     * @param $token
     *
     * @return Collection
     */
    public function logisticsTemplates($token)
    {
        return $this->request('pdd.goods.logistics.template.get', $token);
    }

    //通过pdd.goods.spec.get接口获取对应要发布的商品所属叶子类目所需的规格id；然后根据获取到的生成规格属性id请使用pdd.goods.spec.id.get接口生成商家自定义规格；部分类目已切至新版属性发布，请使用pdd.goods.cat.template.get获取到的规格值发布。

    /**
     *
     * 获取对应要发布的商品所属叶子类目所需的规格id；
     *
     * @param $token
     * @param int $catID  叶子类目ID，必须入参level=3时的cat_id
     *
     * @return Collection
     */
    public function spec($token, $catID)
    {
        return $this->request('pdd.goods.spec.get', $token, ['cat_id' => $catID]);
    }

    /**
     *
     * 通过规格属性id生成商家自定义规格
     *
     * @param $token
     * @param $parentSpecId
     * @param $specName
     *
     * @return Collection
     */
    public function specId($token, $parentSpecId, $specName)
    {
        return $this->request('pdd.goods.spec.id.get', $token, ['parent_spec_id' => $parentSpecId, 'spec_name' => $specName]);
    }

    //商品发布前，通过pdd.goods.cat.template.get查询该类目的商品发布需要的属性，获取商品发布需要的模板-属性-属性值

    /**
     *
     * 查询该类目的商品发布需要的属性
     *
     * @param $token
     * @param $catID
     *
     * @return Collection
     */
    public function catTemplate($token, $catID)
    {
        return $this->request('pdd.goods.cat.template.get', $token, ['cat_id' => $catID]);
    }

    /**
     *
     * 3times/sec, 1200times/day
     *
     * 
     * @param $token
     * @param array $params
     *
     * @return Collection
     */
    public function add($token, array $params)
    {
        return $this->request('pdd.goods.add', $token, $params);
    }

    /**
     *
     * 查询发布的商品的状态
     *
     * @param $token
     * @param $goodsCommitID
     *
     * @return Collection
     */
    public function commitDetail($token, $goodsCommitID)
    {
        return $this->request('pdd.goods.commit.detail.get', $token, ['goods_commit_id' => $goodsCommitID]);
    }

    /**
     *
     * 商品列表查询
     *
     * @see https://open.pinduoduo.com/#/apidocument/port?id=pdd.goods.cat.template.get
     *
     * @param $token
     * @param array $params
     *
     * @return Collection
     */
    public function list($token, array $params = [])
    {
        return $this->request('pdd.goods.list.get', $token, $params);
    }

}