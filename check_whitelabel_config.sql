-- Check whitelabeling settings in ohrm_config
SELECT * FROM ohrm_config 
WHERE `key` LIKE '%company%' 
   OR `key` LIKE '%product%' 
   OR `key` LIKE '%copyright%'
   OR `key` LIKE '%version%';
