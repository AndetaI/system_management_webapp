-- Barkea Resort
-- Seed Data
-- =========================================================
--
-- Contains:
--   4 villa groups
--   8 villa typologies
--   34 villas


--villa groups

INSERT INTO villa_groups (name, description)
VALUES
    ('A', 'Barkea Resort Villa Group A'),
    ('B', 'Barkea Resort Villa Group B'),
    ('C', 'Barkea Resort Villa Group C'),
    ('D', 'Barkea Resort Villa Group D');

--villa typologies

INSERT INTO villa_typologies
    (group_id, name, description, floors, area)
VALUES
(
    (SELECT id FROM villa_groups WHERE name = 'A'),
    'Typology 1',
    'Group A - Typology 1',
    NULL,
    NULL
),
(
    (SELECT id FROM villa_groups WHERE name = 'A'),
    'Typology 2',
    'Group A - Typology 2',
    NULL,
    NULL
),
(
    (SELECT id FROM villa_groups WHERE name = 'B'),
    'Typology 1',
    'Group B - Typology 1',
    NULL,
    NULL
),
(
    (SELECT id FROM villa_groups WHERE name = 'B'),
    'Typology 2',
    'Group B - Typology 2',
    NULL,
    NULL
),
(
    (SELECT id FROM villa_groups WHERE name = 'C'),
    'Typology 1',
    'Group C - Typology 1',
    NULL,
    NULL
),
(
    (SELECT id FROM villa_groups WHERE name = 'C'),
    'Typology 2',
    'Group C - Typology 2',
    NULL,
    NULL
),
(
    (SELECT id FROM villa_groups WHERE name = 'D'),
    'Typology 1',
    'Group D - Typology 1',
    NULL,
    NULL
),
(
    (SELECT id FROM villa_groups WHERE name = 'D'),
    'Typology 2',
    'Group D - Typology 2',
    NULL,
    NULL
);


INSERT INTO villas
    (
        typology_id,
        name,
        status,
        description,
        planned_date,
        actual_date
    )
SELECT
    vt.id,
    mapping.villa_name,
    'planned',
    NULL,
    NULL,
    NULL
FROM (
    SELECT 'A1' AS villa_name, 'A' AS group_name, 'Typology 1' AS typology_name
    UNION ALL SELECT 'A2', 'A', 'Typology 1'
    UNION ALL SELECT 'A3', 'A', 'Typology 1'
    UNION ALL SELECT 'A4', 'A', 'Typology 1'
    UNION ALL SELECT 'A5', 'A', 'Typology 1'
    UNION ALL SELECT 'A6', 'A', 'Typology 1'
    UNION ALL SELECT 'A7', 'A', 'Typology 2'
    UNION ALL SELECT 'A8', 'A', 'Typology 2'

    UNION ALL SELECT 'B1', 'B', 'Typology 1'
    UNION ALL SELECT 'B2', 'B', 'Typology 1'
    UNION ALL SELECT 'B3', 'B', 'Typology 1'
    UNION ALL SELECT 'B5', 'B', 'Typology 1'
    UNION ALL SELECT 'B7', 'B', 'Typology 1'
    UNION ALL SELECT 'B4', 'B', 'Typology 2'
    UNION ALL SELECT 'B6', 'B', 'Typology 2'

    UNION ALL SELECT 'C1', 'C', 'Typology 1'
    UNION ALL SELECT 'C3', 'C', 'Typology 1'
    UNION ALL SELECT 'C5', 'C', 'Typology 1'
    UNION ALL SELECT 'C7', 'C', 'Typology 1'
    UNION ALL SELECT 'C2', 'C', 'Typology 2'
    UNION ALL SELECT 'C4', 'C', 'Typology 2'
    UNION ALL SELECT 'C6', 'C', 'Typology 2'
    UNION ALL SELECT 'C8', 'C', 'Typology 2'

    UNION ALL SELECT 'D1', 'D', 'Typology 1'
    UNION ALL SELECT 'D2', 'D', 'Typology 1'
    UNION ALL SELECT 'D3', 'D', 'Typology 1'
    UNION ALL SELECT 'D4', 'D', 'Typology 1'
    UNION ALL SELECT 'D5', 'D', 'Typology 1'
    UNION ALL SELECT 'D6', 'D', 'Typology 2'
    UNION ALL SELECT 'D7', 'D', 'Typology 2'
    UNION ALL SELECT 'D8', 'D', 'Typology 2'
    UNION ALL SELECT 'D9', 'D', 'Typology 2'
    UNION ALL SELECT 'D10', 'D', 'Typology 2'
    UNION ALL SELECT 'D11', 'D', 'Typology 2'

) AS mapping
INNER JOIN villa_groups vg
    ON vg.name = mapping.group_name
INNER JOIN villa_typologies vt
    ON vt.group_id = vg.id
   AND vt.name = mapping.typology_name;