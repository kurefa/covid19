# 佐賀県新型コロナウイルス感染情報コンバート処理
データをスクレイピングし、data.jsonを生成します。

## Requirement
- php 7.x
- composer
- poppler-utils
- なお、Ubuntu 19.10 (win10 + WSL2)にて動作確認しています。

## Installation
- cd tool
- sudo apt install poppler-utils
- composer install

## Usage
- php convert.php

## Features
佐賀県の新型コロナウイルス感染症の状況
https://www.pref.saga.lg.jp/kiji00373220/index.html

- 上記ページより、添付PDFを./downloads/へダウンロードし、ファイル中の文字をパースします。
- 上記ページより、佐賀県の患者発生状況を取得します。
- これらの情報及び ../data/data.json を元に ./data.json を生成します。
- 生成された内容に問題なければ、 ../data/data.jsonへ上書きしてください。

## Note
- 退院情報
  佐賀県のページには、記者レクに文章としてのみ公開されているため、パースすることができず処理に組み込むことができません。
  残念ですが、退院情報は上記処理を実行後、生成されたdata.jsonを調整する必要があります。
  なお、生成時には既存の退院情報は保持するようにしています。

- 患者発生状況
  佐賀県のページには、症例一覧に発生日がありません。
  ../data/data.jsonと佐賀県のページの件数を比較し、新規データを当日の日付として生成するようにしています。
  佐賀県のページに公開された日付と別の日に処理を実行する場合、背性されたdata.jsonを調整する必要があります。

## Author
[msx2mac](https://github.com/msx2mac)

## Licence
MIT
