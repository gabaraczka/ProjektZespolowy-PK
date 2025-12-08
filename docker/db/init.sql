DROP TABLE IF EXISTS studycards_log;
DROP TABLE IF EXISTS studycards;
DROP TABLE IF EXISTS materials;
DROP TABLE IF EXISTS folders;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS notes;

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE folders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    folder_name TEXT NOT NULL
);

CREATE TABLE materials (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    folder_id INTEGER REFERENCES folders(id) ON DELETE SET NULL,
    material TEXT,
    material_name TEXT NOT NULL,
    material_data BYTEA,
    material_path TEXT
);

CREATE TABLE studycards (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    folder_id INTEGER REFERENCES folders(id) ON DELETE CASCADE,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE studycards_log (
    id SERIAL PRIMARY KEY,
    studycard_id INTEGER REFERENCES studycards(id) ON DELETE CASCADE,
    action TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    folder_id INTEGER REFERENCES folders(id) ON DELETE CASCADE,
    note_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Grant privileges
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO docker;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO docker;
GRANT ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public TO docker;

-- Create indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_folders_user_id ON folders(user_id);
CREATE INDEX idx_materials_folder_id ON materials(folder_id);
CREATE INDEX idx_studycards_user_id ON studycards(user_id);
CREATE INDEX idx_studycards_folder_id ON studycards(folder_id);

-- Widok 1: view_materials_summary

create or replace view view_materials_summary as
select 
    m.id as materialid,
    m.material_name,
    f.folder_name,
    u.email as login
from materials m
join folders f on m.folder_id = f.id
join users u on f.user_id = u.id;


create or replace view view_studycards_info as
select 
    s.id as studycardid,
    s.question as title,
    f.folder_name,
    u.email as login
from studycards s
join folders f on s.folder_id = f.id
join users u on f.user_id = u.id;


create or replace function count_user_cards(integer)
returns integer as $$
declare
    card_count integer;
begin
    select count(*) into card_count
    from studycards s
    join folders f on s.folder_id = f.id
    where f.user_id = $1;
    
    return card_count;
end;
$$ language plpgsql;


create or replace function log_studycard_insert()
returns trigger as $$
begin
    insert into studycards_log(studycard_id, action)
    values (new.id, 'INSERT');
    return new;
end;
$$ language plpgsql;

create trigger trg_studycard_insert
after insert on studycards
for each row
execute function log_studycard_insert();

GRANT ALL PRIVILEGES ON DATABASE db TO docker;

GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO docker;

GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO docker;

GRANT ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public TO docker;

DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'users' AND table_schema = 'public') THEN
        GRANT ALL PRIVILEGES ON TABLE public.users TO docker;
    ELSE
        RAISE NOTICE 'Table "users" does not exist yet. Privileges will be applied when the table is created.';
    END IF;
END $$;

DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'materials' AND table_schema = 'public') THEN
        GRANT ALL PRIVILEGES ON TABLE public.materials TO docker;
    ELSE
        RAISE NOTICE 'Table "materials" does not exist yet. Privileges will be applied when the table is created.';
    END IF;
END $$;

DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'folders' AND table_schema = 'public') THEN
        GRANT ALL PRIVILEGES ON TABLE public.folders TO docker;
    ELSE
        RAISE NOTICE 'Table "folders" does not exist yet. Privileges will be applied when the table is created.';
    END IF;
END $$;

ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL PRIVILEGES ON TABLES TO docker;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL PRIVILEGES ON SEQUENCES TO docker;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL PRIVILEGES ON FUNCTIONS TO docker;

CREATE OR REPLACE FUNCTION grant_users_table_privileges()
RETURNS event_trigger AS $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'users' AND table_schema = 'public') THEN
        EXECUTE 'GRANT ALL PRIVILEGES ON TABLE public.users TO docker';
    END IF;
END;
$$ LANGUAGE plpgsql;

DROP EVENT TRIGGER IF EXISTS ensure_users_privileges;

CREATE EVENT TRIGGER ensure_users_privileges
ON ddl_command_end
WHEN TAG IN ('CREATE TABLE', 'ALTER TABLE')
EXECUTE FUNCTION grant_users_table_privileges();
