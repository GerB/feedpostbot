<?php
/**
 *
 * Feed post bot. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Translated By : Bassel Taha Alhitary <http://alhitary.net>
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}
$lang = array_merge($lang, array(
	'FPB_ACP_FORUM_ID'					=> 'المنتدى',
	'FPB_ACP_FORUM_ID_EXPLAIN'			=> 'سيتم نشر عناصر التغذية الجديدة في هذا المنتدى.',
	'FPB_ACP_SETTINGS_EXPLAIN'			=> 'تستطيع إضافة أنواع التغذية RSS, ATOM أو RDF بإستخدام النموذج أدناه. يجب عليك أولاً إضافة رابط التغذية. بعد إضافة رابط التغذية, سوف تجد جدول بجميع العناوين التالية:',
	'FPB_ACP_FEEDPOSTBOT_SETTING_SAVED'	=> 'تم حفظ الإعدادات بنجاح',
	'FPB_ACP_FEEDPOSTBOT_TITLE'			=> 'قارِئ التغذية للمواقع',
    'FPB_ACP_FETCHED_ITEMS'             => array(
		1	=> 'تم استخراج جميع التغذيات; تم نشر %d موضوع جديد',
		2	=> 'تم استخراج جميع التغذيات: تم نشر %d مواضيع جديدة',
	),
    'FPB_ACP_NO_FETCHED_ITEMS'          => 'لا يوجد عناصر (جديدة) لإستخراجها',
	'FPB_ADD_FEED'						=> 'إضافة تغذية',
	'FPB_APPEND_LINK'					=> 'رابط المصدر',
	'FPB_APPEND_LINK_EXPLAIN'			=> 'إضافة رابط في آخر محتوى الموضوع يشير إلى مصدر التغذية',
    'FPB_CRON_FREQUENCY'				=> 'المُدة (بالثواني) لعملية استخراج التغذيات تلقائياً. القيمة صفر 0 تعني تعطيل هذا الخيار.',
	'FPB_CURDATE'						=> 'تاريخ/وقت محلي',
	'FPB_CURDATE_EXPLAIN'				=> 'التحديد على المربع يعني أن يكون تاريخ استخراج التغذية هو تاريخ الموضوع. عدم التحديد يعني استخدام تاريخ الموضوع الأصلي.',
	'FPB_FETCH_ALL_FEEDS'				=> 'استخراج جميع التغذيات يدوياً',
	'FPB_FEED_TYPE'						=> 'نوع التغذية',
	'FPB_FEED_TYPE_EXPLAIN'				=> 'نوع التغذية يمكن أن تكون ATOM, RDF أو RSS. عند ادخال رابط التغذية لأول مرة, فإنه تلقائياً يتم اكتشاف نوع التغذية. إذا لم يعطي أي نتائج عند عملية استخراج التغذية, حاول تغيير النوع من القائمة المنسدلة هنا.',
	'FPB_FEED_URL'						=> 'رابط التغذية',
	'FPB_FEED_URL_EXPLAIN'				=> 'الرابط الذي يؤدي إلى الرابط الصحيح للتغذية , مثلاً <code>https://www.phpbb.com/feeds/rss/</code>. يجب أن يكون كل رابط مستقل بذاته',
	'FPB_FEED_URL_INVALID'				=> 'رابط التغذية الذي أدخلته غير صالح. محتمل يكون ذلك بسبب رابط مكرر في قائمة التغذية لديك أو أن الرابط يحتوي على حروف غير صالحة',
    'FPB_FEEDS'                         => 'التغذيات',
    'FPB_LOCKED_EXPLAIN'                => 'عملية التغذية بدأت ولكنها لم تكتمل ولذلك لا يمكن أن تبدأ من جديد. إذا استمرت هذه المشكلة تستطيع تنفيذ العملية بالنقر على هذا الزر',
	'FPB_LOG_FEED_ERROR'				=> 'خطأ XML في مصدر “التغذية”<br />» %s',
	'FPB_LOG_FEED_FETCHED'				=> 'تم استخراج “تغذية”<br />» %s',
	'FPB_LOG_FEED_TIMEOUT'				=> 'أنتهت مُهلة “التغذية”<br />» %s',
	'FPB_PREFIX'						=> 'بادئة الموضوع',
	'FPB_PREFIX_EXPLAIN'				=> 'تستطيع إضافة نص قبل عناوين المواضيع, مثال. “[أخبار شبكة الهتاري]”. اتركه فارغاً إذا تريد عدم إضافة البادئة.',
	'FPB_NO_FEEDS'						=> 'لا يوجد روابط تغذية حالياً.',
	'FPB_READ_MORE'						=> 'اقرأ المزيد',
	'FPB_REQUIRE_SIMPLEXML'				=> 'الخدمة PHP <a href="http://php.net/manual/en/book.simplexml.php">SimpleXML</a> غير موجودة في الخادم لديك. الإضافة بحاجة لهذه الخدمة لقراءة التغذيات ولذلك لا يمكن تنصيب الإضافة.',
	'FPB_REQUIRE_URL_FOPEN'				=> 'الإعداد PHP INI <a href="http://php.net/manual/en/filesystem.configuration.php">allow_url_fopen</a> معطل في الخادم لديك. الإضافة بحاجة لهذه الخدمة لقراءة التغذيات ولذلك لا يمكن تنصيب الإضافة.',
	'FPB_SOURCE'						=> 'المصدر:',
	'FPB_TEXTLIMIT'						=> 'حَدّ النص',
	'FPB_TEXTLIMIT_EXPLAIN'				=> 'تستطيع تحديد عدد حروف محتوى التغذية. نرجوا الملاحظة بأنه يتم تطبيق هذه القيمة على النص العادي للتغذية وسيتم حفظ الكلمات كاملة كما هي. بعد ذلك سيتم إصلاح أي أكواد HTML غير صالحة في محتوى التغذية وتحويلها إلى أكواد BBcode وإضافة رابط “اقرأ المزيد” في آخر المحتوى. لذلك تحديد النص هو فقط إشارة لنتيجة نص المشاركة. <br> القيمة صفر 0 تعني تعطيل هذا الخيار.',
	'FPB_TIMEOUT'						=> 'المُهلة',
	'FPB_TIMEOUT_EXPLAIN'				=> 'حدد المهلة لطلب رابط التغذية. إذا تم تجاوز الوقت المحدد بدون استرداد محتوى التغذية, سيتم إلغاء الطلب.',
    'FPB_TYPE_ATOM'						=> 'ATOM',
	'FPB_TYPE_RDF'						=> 'RDF',
	'FPB_TYPE_RSS'						=> 'RSS',
    'FPB_USER_ID'						=> 'رقم العضو',
	'FPB_USER_ID_EXPLAIN'				=> 'رقم العضو الذي سيتم نشر عناصر التغذية بإسمه.',
));
