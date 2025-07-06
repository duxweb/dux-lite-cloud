# DuxLite Cloud Package Manager

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://packagist.org/packages/duxweb/dux-lite-cloud)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

DuxLite v2 云服务扩展包管理器，提供应用包的安装、更新、卸载和发布功能。

## 功能特性

- 📦 **包管理**: 安装、更新、卸载应用包
- 🔐 **认证系统**: 安全的云端认证
- 🌐 **多语言支持**: 自动翻译语言包
- 📤 **包发布**: 发布应用到云端仓库
- 🔄 **依赖管理**: 自动处理PHP和JS依赖
- 🔌 **插件化**: 支持主应用自动注册命令

## 安装

```bash
composer require duxweb/dux-lite-cloud
```

## 命令使用

### 包管理命令

#### 添加包
```bash
# 添加最新版本的包
./dux add package-name

# 添加指定版本的包
./dux add package-name:1.0.0
```

#### 删除包
```bash
./dux del package-name
```

#### 更新包
```bash
# 更新所有包
./dux update

# 更新指定包
./dux update package-name

# 更新指定包到特定版本
./dux update package-name:1.2.0
```

### 应用集合包管理命令

> **说明**: `app:` 开头的命令用于管理应用集合包，一个应用集合包可能包含多个相关的功能包。

#### 安装应用集合包
```bash
# 安装应用集合包
./dux app:add app-name

# 安装应用集合包并编译UI
./dux app:add app-name --build=true
```

#### 卸载应用集合包
```bash
# 卸载应用集合包
./dux app:del app-name

# 卸载应用集合包并编译UI
./dux app:del app-name --build=true
```

#### 更新应用集合包
```bash
# 更新所有应用集合包
./dux app:update

# 更新指定应用集合包
./dux app:update app-name
```

### 开发者命令

#### 发布应用
```bash
# 发布应用到云端仓库
# module-folder 是 app 目录下的模块文件夹名称
# 系统会自动读取该文件夹内的 app.json 配置文件
./dux push module-folder
```

> **示例**: 如果你的模块位于 `app/User/` 目录下，使用 `./dux push user` 命令发布

#### Composer命令
```bash
# 执行composer命令
./dux composer install
./dux composer update
./dux composer require package-name
```

## 配置

### 云端认证
在使用云端功能前，需要配置认证密钥：

```php
// config/use.php
return [
    'key' => 'your-cloud-key',
];
```

## 依赖文件

### app.json (项目根目录)
应用依赖配置文件，记录项目依赖的包和应用：

```json
{
    "name": "project",
    "description": "This is the dux application dependency configuration",
    "dependencies": {
        "package-name": "1.0.0"
    },
    "apps": {
        "app-name": "2023-01-01 00:00:00"
    }
}
```

### app.json (模块目录)
模块配置文件，用于发布时的模块信息配置，位于 `app/ModuleName/app.json`：

```json
{
    "name": "module-package-name",
    "version": "1.0.0",
    "description": "模块描述",
    "author": "作者名称",
    "dependencies": {
        "required-package": "^1.0.0"
    }
}
```

> **注意**: 使用 `./dux push module-folder` 命令时，系统会自动读取对应模块目录下的 `app.json` 文件获取模块信息。

### app.lock
应用锁定文件，记录已安装包的详细信息：

```json
{
    "_readme": [
        "This file relies on the Dux application to be in a locked state",
        "Read more about it at https://www.dux.cn"
    ],
    "packages": [
        {
            "name": "package-name",
            "version": "1.0.0:release",
            "app": "app-name"
        }
    ]
}
```

## 许可证

MIT License


