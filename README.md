FastAdmin是一款基于ThinkPHP5+Bootstrap的极速后台开发框架。（文档地址：https://doc.fastadmin.net/docs  ）

###一、api生成器基本使用

>一键生成curd接口
```bash
php think apicrud -t table -f=true
```

>删除一键生成的curd接口相关文件
```bash
php think apicrud -t table -d 1
```

>生成table表的CRUD且生成关联模型category，外链为category_id，关联表主键为id

```bash
php think apicrud -t table -r category -k category_id -p id
```


>关联多个表,参数传递时请按顺序依次传递，支持以下几个参数relation/relationmodel/relationforeignkey/relationprimarykey/relationfields/relationmode


```bash
php think apicrud -t comments --relation=articles --relation=user --relationforeignkey=article_id --relationforeignkey=user_id
```

###二、行为
