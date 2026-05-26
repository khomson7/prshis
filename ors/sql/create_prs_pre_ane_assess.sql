-- FM-NSO-ANE-001-05: แบบบันทึกทางการพยาบาลการเตรียมผู้ป่วยก่อนให้ยาระงับความรู้สึก
-- Table: prs_pre_ane_assess

CREATE TABLE IF NOT EXISTS prs_pre_ane_assess (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    an              VARCHAR(20) NOT NULL,

    -- Header
    visit_type      VARCHAR(20)  DEFAULT NULL COMMENT 'Elective/OPD/Set เพิ่ม/Emergency',
    visit_place     VARCHAR(20)  DEFAULT NULL COMMENT 'OR/Ward',
    ward            VARCHAR(50)  DEFAULT NULL,
    operation_date  VARCHAR(50)  DEFAULT NULL,

    -- 1. การซักประวัติ
    activity        VARCHAR(50)  DEFAULT NULL COMMENT 'ทำได้ปกติ/มีข้อจำกัด/ต้องนอนบนเตียง',
    fc              VARCHAR(50)  DEFAULT NULL,

    -- 1.2 โรคประจำตัว
    underlying_dm       TINYINT(1) DEFAULT 0,
    underlying_dm_tx    VARCHAR(100) DEFAULT NULL,
    underlying_ht       TINYINT(1) DEFAULT 0,
    underlying_ht_tx    VARCHAR(100) DEFAULT NULL,
    underlying_dlp      TINYINT(1) DEFAULT 0,
    underlying_asthma   TINYINT(1) DEFAULT 0,
    underlying_asthma_tx VARCHAR(100) DEFAULT NULL,
    underlying_heart    TINYINT(1) DEFAULT 0,
    underlying_heart_tx VARCHAR(100) DEFAULT NULL,
    underlying_other    TINYINT(1) DEFAULT 0,
    underlying_other_text VARCHAR(255) DEFAULT NULL,

    -- 1.3 การดื่มสุรา
    alcohol         VARCHAR(20)  DEFAULT NULL,
    alcohol_year    VARCHAR(20)  DEFAULT NULL,

    -- 1.4 การสูบบุหรี่
    smoking         VARCHAR(20)  DEFAULT NULL,
    smoking_year    VARCHAR(20)  DEFAULT NULL,
    smoking_per_day VARCHAR(50)  DEFAULT NULL,

    -- 1.5 การเสพยา,อาหาร,สารเคมี
    allergy         VARCHAR(20)  DEFAULT NULL,
    allergy_detail  VARCHAR(255) DEFAULT NULL,

    -- 1.6 ประวัติการผ่าตัด,การดมยา
    prev_surgery    VARCHAR(20)  DEFAULT NULL,
    prev_surgery_ga TINYINT(1) DEFAULT 0,
    prev_surgery_ra TINYINT(1) DEFAULT 0,
    prev_surgery_date VARCHAR(50) DEFAULT NULL,
    prev_complication TINYINT(1) DEFAULT 0,
    prev_complication_text VARCHAR(255) DEFAULT NULL,

    -- 1.7 ประวัติยา
    medication_history TEXT DEFAULT NULL,

    -- 2. การตรวจร่างกาย
    pe_bw           VARCHAR(20) DEFAULT NULL,
    pe_ht           VARCHAR(20) DEFAULT NULL,
    pe_bmi          VARCHAR(20) DEFAULT NULL,
    pe_temp         VARCHAR(20) DEFAULT NULL,
    pe_pr           VARCHAR(20) DEFAULT NULL,
    pe_rr           VARCHAR(20) DEFAULT NULL,
    pe_bp           VARCHAR(20) DEFAULT NULL,
    pe_spo2         VARCHAR(20) DEFAULT NULL,

    -- 2.1 ตรวจร่างกายทั่วไป
    general_normal  TINYINT(1) DEFAULT 0,
    general_pale    TINYINT(1) DEFAULT 0,
    general_jaundice TINYINT(1) DEFAULT 0,
    general_dry_mouth TINYINT(1) DEFAULT 0,
    general_fatigue TINYINT(1) DEFAULT 0,

    -- 2.2 Consciousness
    consciousness   VARCHAR(20) DEFAULT NULL,

    -- 2.3 Airway
    airway_mouth    VARCHAR(20)  DEFAULT NULL,
    airway_mouth_detail VARCHAR(255) DEFAULT NULL,
    airway_unable   TINYINT(1) DEFAULT 0,
    mallampati      VARCHAR(5)   DEFAULT NULL,
    on_ett_no       VARCHAR(50)  DEFAULT NULL,
    thyromental     VARCHAR(20)  DEFAULT NULL,

    -- 2.4 ฟันปลอม
    denture         VARCHAR(20)  DEFAULT NULL,

    -- 2.5 Heart & Lungs
    heart_lungs     VARCHAR(20)  DEFAULT NULL,
    heart_lungs_detail VARCHAR(255) DEFAULT NULL,

    -- 2.6 Motor Power
    motor_power     VARCHAR(20)  DEFAULT NULL,
    motor_power_detail VARCHAR(255) DEFAULT NULL,

    -- 2.7 Other
    other_exam      VARCHAR(20)  DEFAULT NULL,
    other_exam_detail VARCHAR(255) DEFAULT NULL,

    -- 3. Lab
    lab_status      VARCHAR(20)  DEFAULT NULL,
    lab_hct         VARCHAR(20) DEFAULT NULL,
    lab_hb          VARCHAR(20) DEFAULT NULL,
    lab_plt         VARCHAR(20) DEFAULT NULL,
    lab_other_cbc   VARCHAR(100) DEFAULT NULL,
    lab_fbs         VARCHAR(20) DEFAULT NULL,
    lab_bun         VARCHAR(20) DEFAULT NULL,
    lab_cr          VARCHAR(20) DEFAULT NULL,
    lab_pt          VARCHAR(20) DEFAULT NULL,
    lab_ptt         VARCHAR(20) DEFAULT NULL,
    lab_inr         VARCHAR(20) DEFAULT NULL,
    lab_electrolyte_check VARCHAR(20) DEFAULT NULL,
    lab_na          VARCHAR(20) DEFAULT NULL,
    lab_k           VARCHAR(20) DEFAULT NULL,
    lab_cl          VARCHAR(20) DEFAULT NULL,
    lab_hco3        VARCHAR(20) DEFAULT NULL,
    lab_ua_check    VARCHAR(20) DEFAULT NULL,
    lab_sp_gr       VARCHAR(20) DEFAULT NULL,
    lab_prot        VARCHAR(20) DEFAULT NULL,
    lab_sugar       VARCHAR(20) DEFAULT NULL,
    lab_cxr_check   VARCHAR(20) DEFAULT NULL,
    lab_cxr_result  VARCHAR(20) DEFAULT NULL,
    lab_cxr_detail  VARCHAR(255) DEFAULT NULL,
    lab_ekg_check   VARCHAR(20) DEFAULT NULL,
    lab_ekg_result  VARCHAR(20) DEFAULT NULL,
    lab_ekg_detail  VARCHAR(255) DEFAULT NULL,
    lab_other_check TINYINT(1) DEFAULT 0,
    lab_other_detail VARCHAR(255) DEFAULT NULL,

    -- 4. จองเลือด
    blood_reserve   VARCHAR(20) DEFAULT NULL,
    blood_wb        VARCHAR(20) DEFAULT NULL,
    blood_prc       VARCHAR(20) DEFAULT NULL,
    blood_ffp       VARCHAR(20) DEFAULT NULL,
    blood_plt_unit  VARCHAR(20) DEFAULT NULL,
    blood_cryo      VARCHAR(20) DEFAULT NULL,
    blood_others    VARCHAR(100) DEFAULT NULL,

    -- 5. เซ็นยินยอม
    consent_signed  VARCHAR(20) DEFAULT NULL,

    -- 6. ASA Physical Status
    asa_class       VARCHAR(5)  DEFAULT NULL,

    -- 7. Problem List
    problem_list_status VARCHAR(20) DEFAULT NULL,
    problem_1       VARCHAR(255) DEFAULT NULL,
    problem_2       VARCHAR(255) DEFAULT NULL,
    problem_3       VARCHAR(255) DEFAULT NULL,
    problem_4       VARCHAR(255) DEFAULT NULL,
    problem_5       VARCHAR(255) DEFAULT NULL,
    problem_6       VARCHAR(255) DEFAULT NULL,
    problem_7       VARCHAR(255) DEFAULT NULL,
    problem_8       VARCHAR(255) DEFAULT NULL,
    problem_9       VARCHAR(255) DEFAULT NULL,
    problem_10      VARCHAR(255) DEFAULT NULL,

    -- 8. การแก้ปัญหา/รักษา
    treatment_1     VARCHAR(255) DEFAULT NULL,
    treatment_2     VARCHAR(255) DEFAULT NULL,
    treatment_3     VARCHAR(255) DEFAULT NULL,
    treatment_4     VARCHAR(255) DEFAULT NULL,
    consult_status  VARCHAR(20)  DEFAULT NULL,
    consult_med     TINYINT(1) DEFAULT 0,
    consult_anesth  TINYINT(1) DEFAULT 0,
    premed_1        VARCHAR(255) DEFAULT NULL,
    premed_2        VARCHAR(255) DEFAULT NULL,
    premed_3        VARCHAR(255) DEFAULT NULL,
    premed_4        VARCHAR(255) DEFAULT NULL,
    premed_5        VARCHAR(255) DEFAULT NULL,

    -- 9. Planning
    plan_ga         TINYINT(1) DEFAULT 0,
    plan_ra         TINYINT(1) DEFAULT 0,
    plan_ra_ga      TINYINT(1) DEFAULT 0,
    plan_mac        TINYINT(1) DEFAULT 0,
    plan_tiva       TINYINT(1) DEFAULT 0,
    plan_icu        TINYINT(1) DEFAULT 0,
    spec_a_line     TINYINT(1) DEFAULT 0,
    spec_cvp        TINYINT(1) DEFAULT 0,
    spec_pca        TINYINT(1) DEFAULT 0,
    spec_dlt        TINYINT(1) DEFAULT 0,
    spec_fiberoptic TINYINT(1) DEFAULT 0,
    spec_other      TINYINT(1) DEFAULT 0,
    spec_other_text VARCHAR(100) DEFAULT NULL,

    -- 10. กิจกรรมการพยาบาลก่อนให้ยาระงับความรู้สึก
    act_check_identity TINYINT(1) DEFAULT 0,
    act_assess_asa     TINYINT(1) DEFAULT 0,
    act_explain_anes   TINYINT(1) DEFAULT 0,
    act_plan_anes      TINYINT(1) DEFAULT 0,
    act_check_consent  TINYINT(1) DEFAULT 0,
    act_advice         TEXT DEFAULT NULL,
    act_npo_time       VARCHAR(50) DEFAULT NULL,
    act_npo_denture    VARCHAR(100) DEFAULT NULL,
    act_teach_breathe  TINYINT(1) DEFAULT 0,
    act_prepare_body   TINYINT(1) DEFAULT 0,
    act_pain_advice    TINYINT(1) DEFAULT 0,
    act_other_5        VARCHAR(255) DEFAULT NULL,

    -- 11. กิจกรรมการพยาบาล ห้อง Pre-op
    preop_arrival_time  VARCHAR(20) DEFAULT NULL,
    preop_talk          TINYINT(1) DEFAULT 0,
    preop_vital_sign    TINYINT(1) DEFAULT 0,
    preop_check_identity TINYINT(1) DEFAULT 0,
    preop_check_surgery VARCHAR(255) DEFAULT NULL,
    preop_check_fluid   TINYINT(1) DEFAULT 0,
    preop_no_infiltrate TINYINT(1) DEFAULT 0,
    preop_comfort       TINYINT(1) DEFAULT 0,
    preop_o2_mask       TINYINT(1) DEFAULT 0,
    preop_3way          TINYINT(1) DEFAULT 0,
    preop_other         TINYINT(1) DEFAULT 0,
    preop_other_text    VARCHAR(255) DEFAULT NULL,
    preop_assess_by     VARCHAR(100) DEFAULT NULL,

    -- 12. สรุปประเมินผล
    result_got_surgery      TINYINT(1) DEFAULT 0,
    result_postpone         TINYINT(1) DEFAULT 0,
    result_postpone_reason1 TINYINT(1) DEFAULT 0,
    result_postpone_reason2 TINYINT(1) DEFAULT 0,
    result_postpone_reason3 TINYINT(1) DEFAULT 0,
    result_postpone_reason4 TINYINT(1) DEFAULT 0,
    result_postpone_reason5 TINYINT(1) DEFAULT 0,
    result_postpone_reason6 VARCHAR(255) DEFAULT NULL,

    -- Footer
    diagnosis       TEXT DEFAULT NULL,
    operation_plan  TEXT DEFAULT NULL,
    visit_date      DATE DEFAULT NULL,
    visitor_name    VARCHAR(100) DEFAULT NULL,
    equip_staff     VARCHAR(100) DEFAULT NULL,
    attending_physician VARCHAR(100) DEFAULT NULL,

    -- System
    create_user     VARCHAR(50) DEFAULT NULL,
    update_user     VARCHAR(50) DEFAULT NULL,
    create_datetime DATETIME DEFAULT CURRENT_TIMESTAMP,
    update_datetime DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_deleted      TINYINT(1) DEFAULT 0,
    version         INT DEFAULT 1,

    INDEX idx_an (an),
    INDEX idx_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='FM-NSO-ANE-001-05 แบบบันทึกทางการพยาบาลการเตรียมผู้ป่วยก่อนให้ยาระงับความรู้สึก';
