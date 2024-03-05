CREATE EXTENSION IF NOT EXISTS pg_trgm;
CREATE EXTENSION fuzzystrmatch;
create extension btree_gin;
create index tic_con_names_pg on per_initials_combinations using gin (initial_combination gin_trgm_ops);
