# CLAUDE.md

## Project Overview

Ray.DIの依存性注入機能とPSR-11 (ContainerInterface) 標準を橋渡しするアダプターライブラリ。

## Development Commands

```bash
composer test              # テスト実行
composer cs                # コーディング規約チェック
composer cs-fix            # コーディング規約の自動修正
composer sa                # 静的解析（PHPStan + Psalm + PHPMD）
composer tests             # 全品質チェック（cs + sa + test）
composer build             # ビルド全体（clean + cs + sa + pcov）
```

## Key Architecture

PSR-11の文字列IDとRay.DIのインターフェース+名前ペアを変換する薄いアダプター層。

**ID形式:**
- `Interface::class#Name` - インターフェース + 修飾子名
- `Interface::class` - インターフェースのみ
- `#name` - 名前のみ

**例外マッピング:**
Ray.DIの例外をPSR-11標準例外にマッピング（`NotFoundExceptionInterface`、`ContainerExceptionInterface`）