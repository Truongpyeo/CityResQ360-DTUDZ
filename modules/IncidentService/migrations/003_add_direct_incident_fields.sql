-- Add new fields to incidents table for direct incident creation
-- This allows IncidentService to work independently without CoreAPI report_id

ALTER TABLE incidents 
  ALTER COLUMN report_id DROP NOT NULL,
  DROP CONSTRAINT IF EXISTS incidents_report_id_key;

ALTER TABLE incidents
  ADD COLUMN IF NOT EXISTS title VARCHAR(255),
  ADD COLUMN IF NOT EXISTS description TEXT,
  ADD COLUMN IF NOT EXISTS location_latitude DECIMAL(10, 8),
  ADD COLUMN IF NOT EXISTS location_longitude DECIMAL(11, 8),
  ADD COLUMN IF NOT EXISTS address VARCHAR(500),
  ADD COLUMN IF NOT EXISTS category VARCHAR(100),
  ADD COLUMN IF NOT EXISTS external_id VARCHAR(100),
  ADD COLUMN IF NOT EXISTS external_system VARCHAR(50);

-- Create index for external tracking
CREATE INDEX IF NOT EXISTS idx_incidents_external_id ON incidents(external_id);
CREATE INDEX IF NOT EXISTS idx_incidents_location ON incidents(location_latitude, location_longitude);

-- Add comments
COMMENT ON COLUMN incidents.report_id IS 'Optional - CoreAPI report ID (null for direct creation)';
COMMENT ON COLUMN incidents.title IS 'Required - Incident title';
COMMENT ON COLUMN incidents.description IS 'Required - Incident description';
COMMENT ON COLUMN incidents.location_latitude IS 'Optional - Incident location latitude';
COMMENT ON COLUMN incidents.location_longitude IS 'Optional - Incident location longitude';
COMMENT ON COLUMN incidents.address IS 'Optional - Human-readable address';
COMMENT ON COLUMN incidents.category IS 'Optional - Category name';
COMMENT ON COLUMN incidents.external_id IS 'Optional - External system tracking ID';
COMMENT ON COLUMN incidents.external_system IS 'Optional - External system name (e.g., "iot_sensor", "mobile_v2")';
