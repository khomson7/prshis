CREATE TABLE IF NOT EXISTS psh_caprini_score (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hn VARCHAR(20) NOT NULL,
    an VARCHAR(20) NOT NULL,
    assessment_date DATETIME NOT NULL,
    total_score INT DEFAULT 0,
    risk_level VARCHAR(50),
    
    age_range VARCHAR(20) DEFAULT '<41', -- <41, 41-60, 61-74, >=75

    -- 1 point items
    minor_surgery TINYINT(1) DEFAULT 0,
    bmi_over_25 TINYINT(1) DEFAULT 0,
    swollen_legs TINYINT(1) DEFAULT 0,
    varicose_veins TINYINT(1) DEFAULT 0,
    pregnancy_postpartum TINYINT(1) DEFAULT 0,
    history_unexplained_miscarriage TINYINT(1) DEFAULT 0,
    oral_contraceptives_hrt TINYINT(1) DEFAULT 0,
    sepsis_1_month TINYINT(1) DEFAULT 0,
    severe_lung_disease TINYINT(1) DEFAULT 0,
    abnormal_pulmonary_function TINYINT(1) DEFAULT 0,
    ischemic_heart_disease TINYINT(1) DEFAULT 0,
    chf_1_month TINYINT(1) DEFAULT 0,
    inflammatory_bowel_disease TINYINT(1) DEFAULT 0,
    bed_rest TINYINT(1) DEFAULT 0,

    -- 2 points items
    arthroscopic_surgery TINYINT(1) DEFAULT 0,
    major_surgery_over_45m TINYINT(1) DEFAULT 0,
    laparoscopic_surgery_over_45m TINYINT(1) DEFAULT 0,
    cancer_patient TINYINT(1) DEFAULT 0,
    bedridden_over_72h TINYINT(1) DEFAULT 0,
    patient_in_cast TINYINT(1) DEFAULT 0,
    central_venous_access TINYINT(1) DEFAULT 0,

    -- 3 points items
    history_vte TINYINT(1) DEFAULT 0,
    family_history_vte TINYINT(1) DEFAULT 0,
    factor_v_leiden TINYINT(1) DEFAULT 0,
    prothrombin_20210a TINYINT(1) DEFAULT 0,
    lupus_anticoagulant TINYINT(1) DEFAULT 0,
    anticardiolipin_antibodies TINYINT(1) DEFAULT 0,
    increased_homocysteine TINYINT(1) DEFAULT 0,
    heparin_induced_thrombocytopenia TINYINT(1) DEFAULT 0,
    thrombophilia TINYINT(1) DEFAULT 0,

    -- 5 points items
    stroke_1_month TINYINT(1) DEFAULT 0,
    elective_major_lower_extremity_arthroplasty TINYINT(1) DEFAULT 0,
    hip_pelvis_leg_fracture TINYINT(1) DEFAULT 0,
    acute_spinal_cord_injury_1_month TINYINT(1) DEFAULT 0,

    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
