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

### 开发者命令

#### 发布应用
```bash
# 发布应用到云端仓库
# module-folder 是 app 目录下的模块文件夹名称
# 系统会自动读取该文件夹内的 app.json 配置文件
./dux push module-folder
```

> **示例**: 如果你的模块位于 `app/User/` 目录下，使用 `./dux push user` 命令发布

**发布流程**:
1. 输入新版本号
2. 输入更新日志（支持多行）：
   - 支持多行输入，每行一个更新项
   - 连续按两次回车结束输入
   - 如果不输入任何内容，系统会自动添加默认的 "- Update" 记录
3. 系统会自动生成或更新模块目录下的 `CHANGELOG.md` 文件
4. 自动打包并上传到云端仓库

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

### CHANGELOG.md (模块目录)
模块更新日志文件，记录版本变更历史，位于 `app/ModuleName/CHANGELOG.md`：

```markdown
# Changelog

All notable changes to this project will be documented in this file.

## [1.2.0] - 2024-01-15

- 新增用户权限管理功能
- 修复登录状态检查bug
- 优化数据库查询性能

## [1.1.0] - 2024-01-10

- 添加多语言支持
- 更新UI界面设计

## [1.0.0] - 2024-01-05

- Update
```

> **自动生成**: 使用 `./dux push` 命令发布时，如果输入了更新日志，系统会自动生成或更新此文件。

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

