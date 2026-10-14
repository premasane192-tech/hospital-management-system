<?php
/**
 * Minimal translation layer for the printable/downloadable health report.
 * Only report-template labels are translated (structured UI text) — free-text
 * fields entered by the doctor/admin (clinical notes, recommendations, follow-up,
 * medical reference interpretations) are stored and shown as originally entered,
 * since those are open-ended clinical text rather than fixed labels.
 */

const SUPPORTED_LANGS = ['en' => 'English', 'hi' => 'हिन्दी', 'mr' => 'मराठी'];

function current_report_lang(): string {
    $lang = $_GET['lang'] ?? 'en';
    return array_key_exists($lang, SUPPORTED_LANGS) ? $lang : 'en';
}

function t(string $key, ?string $lang = null): string {
    static $strings = [
        'report_subtitle' => [
            'en' => 'Digital Health Report',
            'hi' => 'डिजिटल स्वास्थ्य रिपोर्ट',
            'mr' => 'डिजिटल आरोग्य अहवाल',
        ],
        'report_id' => [
            'en' => 'Report ID',
            'hi' => 'रिपोर्ट आईडी',
            'mr' => 'अहवाल आयडी',
        ],
        'date' => [
            'en' => 'Date',
            'hi' => 'दिनांक',
            'mr' => 'दिनांक',
        ],
        'patient_id' => [
            'en' => 'Patient ID',
            'hi' => 'रोगी आईडी',
            'mr' => 'रुग्ण आयडी',
        ],
        'patient_name' => [
            'en' => 'Patient Name',
            'hi' => 'रोगी का नाम',
            'mr' => 'रुग्णाचे नाव',
        ],
        'age_gender' => [
            'en' => 'Age / Gender',
            'hi' => 'आयु / लिंग',
            'mr' => 'वय / लिंग',
        ],
        'contact' => [
            'en' => 'Contact',
            'hi' => 'संपर्क',
            'mr' => 'संपर्क',
        ],
        'yrs' => [
            'en' => 'yrs',
            'hi' => 'वर्ष',
            'mr' => 'वर्षे',
        ],
        'test_results_heading' => [
            'en' => 'Test Results & Analysis',
            'hi' => 'जाँच परिणाम एवं विश्लेषण',
            'mr' => 'चाचणी निकाल आणि विश्लेषण',
        ],
        'test' => [
            'en' => 'Test',
            'hi' => 'जाँच',
            'mr' => 'चाचणी',
        ],
        'value' => [
            'en' => 'Value',
            'hi' => 'मान',
            'mr' => 'मूल्य',
        ],
        'unit' => [
            'en' => 'Unit',
            'hi' => 'इकाई',
            'mr' => 'एकक',
        ],
        'reference_range' => [
            'en' => 'Reference Range',
            'hi' => 'संदर्भ सीमा',
            'mr' => 'संदर्भ श्रेणी',
        ],
        'status' => [
            'en' => 'Status',
            'hi' => 'स्थिति',
            'mr' => 'स्थिती',
        ],
        'no_results' => [
            'en' => 'No test results recorded for this report.',
            'hi' => 'इस रिपोर्ट के लिए कोई जाँच परिणाम दर्ज नहीं है।',
            'mr' => 'या अहवालासाठी कोणतेही चाचणी निकाल नोंदवलेले नाहीत.',
        ],
        'status_low' => [ 'en' => 'Low', 'hi' => 'कम', 'mr' => 'कमी' ],
        'status_normal' => [ 'en' => 'Normal', 'hi' => 'सामान्य', 'mr' => 'सामान्य' ],
        'status_high' => [ 'en' => 'High', 'hi' => 'अधिक', 'mr' => 'जास्त' ],
        'medical_reference_heading' => [
            'en' => 'Medical Reference & General Interpretation',
            'hi' => 'चिकित्सा संदर्भ एवं सामान्य व्याख्या',
            'mr' => 'वैद्यकीय संदर्भ आणि सर्वसाधारण अर्थ',
        ],
        'doctor_notes_heading' => [
            'en' => "Doctor's Clinical Notes",
            'hi' => 'डॉक्टर के नैदानिक ​​नोट्स',
            'mr' => 'डॉक्टरांच्या क्लिनिकल नोंदी',
        ],
        'recommendations_heading' => [
            'en' => 'Recommendations',
            'hi' => 'सिफारिशें',
            'mr' => 'शिफारशी',
        ],
        'follow_up_heading' => [
            'en' => 'Follow-up Instructions',
            'hi' => 'अनुवर्ती निर्देश',
            'mr' => 'पाठपुरावा सूचना',
        ],
        'no_doctor' => [
            'en' => 'No reviewing doctor recorded for this report.',
            'hi' => 'इस रिपोर्ट के लिए कोई समीक्षक डॉक्टर दर्ज नहीं है।',
            'mr' => 'या अहवालासाठी कोणताही पुनरावलोकन डॉक्टर नोंदवलेला नाही.',
        ],
        'reg_no' => [
            'en' => 'Reg. No',
            'hi' => 'पंजीकरण सं.',
            'mr' => 'नोंदणी क्र.',
        ],
        'doctor_signature' => [
            'en' => "Doctor's Signature",
            'hi' => 'डॉक्टर के हस्ताक्षर',
            'mr' => 'डॉक्टरांची स्वाक्षरी',
        ],
        'disclaimer_label' => [
            'en' => 'Medical Disclaimer:',
            'hi' => 'चिकित्सा अस्वीकरण:',
            'mr' => 'वैद्यकीय अस्वीकरण:',
        ],
        'disclaimer_text' => [
            'en' => 'This report is generated for informational and record-keeping purposes based on the values entered at the time of testing. It is not a substitute for professional medical diagnosis or treatment. Please consult the reviewing doctor or a qualified physician to interpret these results in the context of your full clinical history before making any health decisions.',
            'hi' => 'यह रिपोर्ट जाँच के समय दर्ज किए गए मानों के आधार पर सूचना एवं रिकॉर्ड-रखरखाव के उद्देश्य से तैयार की गई है। यह पेशेवर चिकित्सा निदान या उपचार का विकल्प नहीं है। कोई भी स्वास्थ्य निर्णय लेने से पहले कृपया इन परिणामों की व्याख्या अपने पूर्ण नैदानिक इतिहास के संदर्भ में करने के लिए समीक्षक डॉक्टर या किसी योग्य चिकित्सक से परामर्श करें।',
            'mr' => 'हा अहवाल चाचणीच्या वेळी नोंदवलेल्या मूल्यांच्या आधारे माहिती व नोंद ठेवण्याच्या उद्देशाने तयार करण्यात आला आहे. हा व्यावसायिक वैद्यकीय निदान किंवा उपचारांचा पर्याय नाही. कोणताही आरोग्यविषयक निर्णय घेण्यापूर्वी कृपया आपल्या संपूर्ण वैद्यकीय इतिहासाच्या संदर्भात हे निकाल समजून घेण्यासाठी संबंधित डॉक्टर किंवा पात्र वैद्यकीय तज्ज्ञांचा सल्ला घ्या.',
        ],
        'print_button' => [
            'en' => 'Print / Save as PDF',
            'hi' => 'प्रिंट करें / पीडीएफ के रूप में सहेजें',
            'mr' => 'प्रिंट करा / पीडीएफ म्हणून जतन करा',
        ],
        'language' => [
            'en' => 'Language',
            'hi' => 'भाषा',
            'mr' => 'भाषा',
        ],
    ];

    $lang = $lang ?? current_report_lang();
    return $strings[$key][$lang] ?? $strings[$key]['en'] ?? $key;
}

/** Translated status label, given the English status stored in DB ('Low'/'Normal'/'High') */
function t_status(string $status, ?string $lang = null): string {
    return match ($status) {
        'Low'  => t('status_low', $lang),
        'High' => t('status_high', $lang),
        default => t('status_normal', $lang),
    };
}
