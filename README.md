# Atte

# 概要

このアプリケーションは、従業員の勤怠を管理するためのシンプルなアプリケーションです。

![トップ画面の画像](https://github.com/yabe-shiori/atte/assets/142664073/f2f4716f-0a24-4d27-82bd-a33f3c561856)


## 目的

この勤怠管理アプリは、企業や組織が従業員の出勤、退勤時間を追跡し、効率的に勤怠を管理することを目的としています。
以下は、アプリケーションが提供する主な機能です。

 ## 特長

- ユーザー登録とログイン機能
- 勤怠の打刻と記録
- 自分の勤怠情報の把握
- 管理者における従業員の勤怠の確認

## インストール

1.プロジェクトのクローン  
`git clone https://github.com/yabe-shiori/atte.git atte`  
  
プロジェクトディレクトリに移動します。    
`cd atte`  

2.環境変数の設定
.env.exampleファイルをコピーして.envファイルを作成し、必要な環境変数を設定します。  
`cp .env.example .env`  

DB_CONNECTION=mysql  
DB_HOST=mysql  
DB_PORT=3306  
DB_DATABASE=atte  
DB_USERNAME=sail  
DB_PASSWORD=password  

3.Composerパッケージのインストール  
`composer install`

4.Docker環境のセットアップ  
laravelSailを使用してDocker環境をセットアップします。  
`./vendor/bin/sail up -d`    

5.アプリケーションキーの生成  
`./vendor/bin/sail artisan key:generate`    
  
6.NPMパッケージのインストール  
`./vendor/bin/sail npm install`    

7.アセットのコンパイル  
`./vendor/bin/sail npm run dev`        

8.アプリケーションの実行  
・Webブラウザで[http://localhost](http://localhost)にアクセスして、アプリケーションが正しく動作していることを確認します。  

  
  
**管理者ユーザーの追加** 
  
1.会員登録  
- 名前 -> テスト太郎  
- メールアドレス testtaro@test.com  
- パスワード -> 12345678  
を入力して会員登録する  
  
2.phpMyAdminにログインし、roleテーブルとrole_userテーブルに登録する。 
- rolesテーブル  
id1 name->admin   
id2 name->user    
を挿入する。

- role_userテーブルに挿入
user1のテスト太郎に、role_id1のadminの役割を付与する。

注意事項:
管理者としてログインする場合、上記で設定したテスト太郎さんを利用してログインすることによって管理者専用の機能が利用できます。

**メール通知について**  
MailPitを利用しています。  
[http://localhost:8025](http://localhost:8025)にアクセスして通知メールを確認してください。  

## 機能一覧
| 会員登録画面 |　ログイン画面 |
| ---- | ---- |
| ![会員登録画面](https://github.com/yabe-shiori/atte/assets/142664073/74610f84-65d9-45e1-8556-52ec6931fcab) | ![ログイン画面](https://github.com/yabe-shiori/atte/assets/142664073/a26fb8b9-4097-4a44-bbfd-09eaf796fa49) |
| 従業員は会員登録を行います。 | メールアドレスとパスワードを入力し、ログインします。 |

| メール認証 | 打刻画面 |
| ---- | ---- |
| ![メール認証](https://github.com/yabe-shiori/atte/assets/142664073/68dad4e6-5597-4b5a-be21-fd2b53437f28)　| ![打刻画面](https://github.com/yabe-shiori/atte/assets/142664073/14f71903-b82a-494e-b42f-d2dbb34d2a94) |
| 安全に使用できるようにメール認証機能を実装しました。 | 従業員はログイン後に自分の勤怠情報をワンクリックで登録できます。 |


| 従業員ごとの勤怠情報画面 |　アカウント編集画面 |
| ---- | ---- |
| ![自分の勤怠情報確認画面](https://github.com/yabe-shiori/atte/assets/142664073/38740632-4d68-4a48-8963-45440f9103b3) | ![アカウント情報編集ページ](https://github.com/yabe-shiori/atte/assets/142664073/18185326-6125-4806-a7a4-fe58a5225214) | 
| 従業員は自分の勤怠情報を月別で確認できます。 | 従業員は自分のメールアドレスやパスワード情報を編集することができます。 |

| 打刻忘れ通知 |　従業員一覧画面 |
| ---- | ---- |
| ![打刻忘れ通知](https://github.com/yabe-shiori/atte/assets/142664073/11e267e7-7a24-4b72-9f5c-8f4a3f72b960) | ![従業員一覧画面](https://github.com/yabe-shiori/atte/assets/142664073/c72f75d5-c164-4b7e-ac51-1c8c3c917394)|
| 勤務開始から一定時間経過しても勤務終了ボタンが押されていない場合は、従業員にメールでお知らせします。 | 管理者は登録している従業員を確認することができます。 |

| 勤怠編集画面 |　役割付与 |
| ---- | ---- |
| ![勤怠編集画面](https://github.com/yabe-shiori/atte/assets/142664073/129c5422-9f08-4d62-8913-62437363425c)| ![　役割付与画面](https://github.com/yabe-shiori/atte/assets/142664073/3d23e1b0-476a-467f-970f-bfe3c21da4b8) |
| 管理者は従業員が勤務終了ボタンを押し忘れた際などに、適切に勤務情報を編集することができます。 | 管理者が従業員に役割を付与する機能を実装しました。 |

<br />

## 使用技術

| Category          | Technology Stack                                     |
| ----------------- | --------------------------------------------------   |
| Frontend          | npm, Tailwind CSS                                    |
| Backend           | Laravel, PHP                                         |
| Infrastructure    | Amazon Web Services                                  |
| Database          | MySQL                                                |
| Environment setup | Docker, Laravel Sail                                 |
| etc.              | Git, GitHub                                          |

<br />

## ER図

![ER図](https://github.com/yabe-shiori/atte/assets/142664073/9bff8fd7-7876-4480-b097-adb96b4490fd)

<br />

