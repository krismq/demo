<font size=5 color=5 face="微软雅黑" >
## 打算在本地（公司）拉去GitHub中的一个项目，计划是将每天的收获更新到github上此项目。然而实际情况是这样的，在本地创建了一个目录magit，进入该目录中，右击鼠标，选择 `Git Bush Here` 命令，进入git界面，开始输入命令 `git clone  项目的url`，不想看到的情况发生了，如图：
![](https://i.imgur.com/EXBgs8m.png)

---

###多方查证，原因是：很久之前，用本人自己的电脑拉去github上的项目，GitHub上的公钥是自己电脑的
- 
![](https://i.imgur.com/5tZc5y1.png)

###目前所知情况，只能删掉了`（想法：可不可以不删除，能不能添加新的公钥呢？这样的话，自己在家或公司时，发现新鲜的东西，都可以上传的呀。`

1. 进入本地创建的目录magit，进入GitHub界面，输入 `git init`  生成一个 .git的目录
![](https://i.imgur.com/hH9KE1y.png)

2. 创建公钥：输入命令  `ssh-keygen -t -C “GitHub上注册的邮箱”`
![](https://i.imgur.com/70Qrdbv.png)

3. 查看有没有生成公钥和私钥： cd ~/.ssh
![](https://i.imgur.com/zWkr4BG.png)
`id_rsa为私钥；id_rsa.pub为公钥；`
4. 在GitHub上添加公钥

----------

![](https://i.imgur.com/XEyvMwF.png)

----------

![](https://i.imgur.com/jPmn5Yv.png)

----------

![](https://i.imgur.com/UzasnUP.png)


----------

5.测试是否配置成功`ssh -T git@github.com`如图即为配置成功。
![](https://i.imgur.com/zJYXAk2.png)

到此就可以，拉去GitHub上的项目了
![](https://i.imgur.com/XU7166j.png)

    