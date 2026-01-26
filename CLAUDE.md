# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Ray.RayDiPsrContainerは、Ray.DIの依存性注入機能を活用しながらPSR-11 (ContainerInterface) 互換のインターフェースを提供するPHPライブラリです。このライブラリは、Ray.DIの高度な機能（属性ベースの修飾子、遅延読み込み、コンパイル最適化）とPSR-11標準の間のアダプター層として機能します。

## Development Commands

### Testing
```bash
composer test              # PHPUnitテストを実行
composer coverage          # カバレッジレポートを生成（Xdebug使用）
composer pcov              # カバレッジレポートを生成（PCOV使用、CI推奨）
```

### Code Quality
```bash
composer cs                # コーディング規約チェック（PHPCS）
composer cs-fix            # コーディング規約の自動修正（PHPCBF）
composer sa                # 静的解析（PHPStan + Psalm + PHPMD）
composer clean             # キャッシュクリア
```

### Workflows
```bash
composer tests             # 全品質チェック（cs + sa + test）
composer build             # ビルド全体（clean + cs + sa + pcov）
```

### Running Single Tests
```bash
vendor/bin/phpunit --filter testMethodName
vendor/bin/phpunit tests/PsrContainerTest.php
```

## Architecture & Key Components

### Core Classes

#### `PsrContainer` (src/PsrContainer.php)
PSR-11の`ContainerInterface`を実装し、Ray.DIの`InjectorInterface`をラップする中核クラス。

**拡張ID形式:**
- `Interface::class` → インターフェースのみ（名前は`Name::ANY`）
- `Interface::class#Name` → インターフェース + 修飾子名
- `#name` → 名前のみ（インターフェースなし）

**例外マッピング:**
- Ray.DIの`Unbound`例外 → `NaokiTsuchiya\RayDiPsrContainer\Exception\Unbound`（PSR-11の`NotFoundExceptionInterface`を実装）
- その他の例外 → `ContainerException`（PSR-11の`ContainerExceptionInterface`を実装）

**バリデーション例外（`InvalidIdException`）:**
- 空文字列のID → "id must not be empty."
- セパレーターのみ（`#`） → "id must not be only a separator."
- 存在しないクラス/インターフェース名 → "[名前] is not a class name or interface name"

#### `IdentityParser` (src/IdentityParser.php)
拡張ID文字列をRay.DIのインターフェース+名前ペアに解析する内部ユーティリティ。

**定数:**
- `NAME_SEPARATOR` - ID文字列のセパレーター（`#`）

**機能:**
- `#`を区切り文字として使用
- クラス/インターフェース名の存在を`class_exists()`/`interface_exists()`で検証
- 無効なIDの場合は`InvalidIdException`を投げる

#### `IdentityStringGenerator` (src/IdentityStringGenerator.php)
`PsrContainer`で使用する正しい形式のID文字列を生成するヘルパークラス。

```php
// 例1: インターフェースと修飾子の組み合わせ（実際のテストコードより）
IdentityStringGenerator::generate(FakeLegInterface::class, Left::class)
// 結果: "NaokiTsuchiya\RayDiPsrContainer\FakeLegInterface#NaokiTsuchiya\RayDiPsrContainer\Attribute\Left"

// 例2: 名前のみ
IdentityStringGenerator::generate('', 'name')
// 結果: "#name"

// 例3: インターフェースのみ（名前はName::ANYがデフォルト）
IdentityStringGenerator::generate(FakeRobotInterface::class)
// 結果: "NaokiTsuchiya\RayDiPsrContainer\FakeRobotInterface#"
```

### Ray.DI Integration Pattern

```
PSR-11 Application
      ↓ get(id) / has(id)
PsrContainer（PSR-11ラッパー）
      ↓ IdentityParserで解析
Ray.DI Injector
      ↓ getInstance(interface, name)
依存性注入されたインスタンス
```

### Module System (Tests)

Ray.DIモジュールは`AbstractModule`を継承し、`configure()`メソッドでバインディングを定義:

```php
protected function configure() {
    // インターフェースから実装へのバインディング
    $this->bind(Interface::class)->to(Implementation::class);

    // 修飾子付きバインディング（複数の実装を区別）
    $this->bind(Interface::class)
         ->annotatedWith(Left::class)
         ->to(LeftImplementation::class);

    // 名前付きインスタンス
    $this->bind()->annotatedWith('name')->toInstance('value');
}
```

**Qualifier属性:**
同じインターフェースの複数のバインディングを区別するために使用:

```php
#[Attribute]
#[Qualifier]
final class Left { }
```

### Test Structure

- **tests/PsrContainerTest.php** - `PsrContainer`の統合テスト
- **tests/IdentityStringGeneratorTest.php** - ID生成のテスト
- **tests/Fake/** - テスト用フィクスチャ
  - `FakeModule.php` - バインディング設定のサンプル
  - `FakeRobotInterface/FakeRobot` - シンプルなインターフェース/実装
  - `FakeLegInterface/FakeLeg` - 修飾子付きバインディングのデモ
  - `Left/Right` - Qualifier属性の例

## CI/CD

GitHub Actionsワークフロー:
- **continuous-integration.yml** - PHP 8.0-8.4、highest/lowest依存関係でのマトリックステスト
- **static-analysis.yml** - PHPStan、Psalm、PHPMDによる静的解析
- **coding-standard.yml** - PHPCSによるコーディング規約チェック
- **release.yml** - リリース自動化

## Development Tools

- **PHPUnit 9.6** - ユニットテスト
- **PHPStan (max level)** - 静的解析（bleedingEdge設定）
- **Psalm** - 追加の静的解析
- **PHPMD** - メスディテクション
- **PHPCS/PHPCBF** - コーディング規約
- **bamarni/composer-bin-plugin** - 開発ツールの依存関係分離（vendor-bin/）

## Key Design Principles

1. **薄いアダプター層**: PSR-11のシンプルな文字列ベースインターフェースとRay.DIのインターフェース+名前ベースシステムの間の変換のみを行う
2. **PSR-11完全準拠**: `psr/container-implementation: ^2.0`を提供し、PSR-11 ContainerInterface標準に完全準拠
3. **拡張性**: `#`区切り文字を使用した拡張ID形式により、Ray.DIの全機能（修飾子、属性）へのアクセスを可能にする
4. **例外の互換性**: Ray.DIの例外をPSR-11標準の例外インターフェース（`NotFoundExceptionInterface`、`ContainerExceptionInterface`）にマッピング
5. **開発/本番対応**: `Injector`（開発用）と`CompileInjector`（コンパイル済み最適化、本番用）の両方をサポート

### 本番環境での最適化

```php
use Ray\Compiler\CompileInjector;

// CompileInjectorを使用した最適化
$injector = new CompileInjector('/path/to/cache', new YourModule());
$container = new PsrContainer($injector);
```