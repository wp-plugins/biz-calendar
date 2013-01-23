=== Biz Calendar ===
Contributors: WordPress BizPlugin
Donate link:
Tags: calendar,event,widget
Requires at least: 3.4
Tested up to: 3.5
Stable tag: 1.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Business day and event calendar on widget.

== Description ==

Biz Calendarは、営業日・イベントカレンダーをウィジェットに表示するプラグインです。
休業日、イベント開催日を表示するカレンダーを サイドメニューに簡単に作成できます。

= 特徴 =

* 曜日を指定して定休日に設定できます
* 祝日を自動的に定休日に設定できます
    * Google calendar APIから祝日情報を取得しています。Google calendar APIが使用できない環境では利用できません
* 臨時休業日・臨時営業日を登録できます
* イベント開催日、urlを設定できます
* ページを移動せずに翌月・前月のカレンダーを表示できます

詳細な使用方法はこちらです: http://residentbird.main.jp/bizplugin/plugins/bizcalendar/

== Installation ==

1. Upload `plugin-name.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Calendar widget
2. Admin page

== Changelog ==

= 1.4.0 =
* カレンダーのデザインを変更しました

= 1.3.0 =
* 今月のカレンダーに戻る機能を追加しました

= 1.2.0 =
* 祝日の自動表示期間を年（1月～12月末まで）から年度（4月～翌年3月末まで）に変更しました
* Google Calendar APIからの祝日取得結果をキャッシュし、表示パフォーマンスを向上しました

= 1.1.0 =
* カレンダーの今日を強調して表示する機能追加
* イベントの説明をイベントのurlへのリンクになる機能追加
* 障害修正

= 1.0.0 =
* 初版リリース
