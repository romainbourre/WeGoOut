create table if not exists ADJECTIVE
(
    ADJ_ID   tinyint auto_increment
        primary key,
    ADJ_NAME char(20) null
)
    charset = latin1;

create table if not exists CATEGORY
(
    CAT_ID   tinyint auto_increment
        primary key,
    CAT_NAME varchar(20) not null
)
    charset = latin1;

create table if not exists COMJUDGE
(
    COM_JUD_ID       mediumint auto_increment
        primary key,
    COM_JUD_DATETIME date         not null,
    COM_JUD_TEXT     varchar(255) not null,
    COM_JUD_VALID    int          not null
)
    charset = latin1;

create table if not exists GUEST
(
    USER_ID               mediumint not null,
    EVENT_ID              mediumint not null,
    GUEST_DATETIME_SEND   datetime  null,
    GUEST_DATETIME_DELETE datetime  null
)
    charset = utf8mb3;

create table if not exists GUEST_TEMP_EMAIL
(
    GUEST_EMAIL           text     null,
    GUEST_DATETIME_SEND   datetime null,
    GUEST_DATETIME_DELETE datetime null,
    EVENT_ID              int      not null
);

create table if not exists NOTIFICATIONS
(
    NOTIF_ID            bigint auto_increment
        primary key,
    NOTIF_MESSAGE       varchar(200) default '' not null,
    NOTIF_CATEGORY      tinyint                 null,
    USER_ID             mediumint               not null,
    NOTIF_ACTION        varchar(100)            null,
    NOTIF_DATETIME_SEND datetime                not null,
    NOTIF_DATETIME_READ datetime                null,
    NOTIF_VALID         tinyint                 null,
    NOTIF_TYPE          tinyint                 not null,
    NOTIF_TARGET_EVENT  int                     null,
    NOTIF_TARGET_USER   int                     null
)
    charset = utf8mb3;

create table if not exists PARTICIPATE
(
    USER_ID              mediumint not null,
    EVENT_ID             mediumint not null,
    PART_DATETIME_SEND   datetime  not null,
    PART_DATETIME_ACCEPT datetime  null,
    PART_DATETIME_DELETE datetime  null
)
    charset = latin1;

create table if not exists REVIEW
(
    REV_ID              mediumint unsigned auto_increment
        primary key,
    EVENT_ID            mediumint            not null,
    USER_ID             mediumint            not null,
    REV_NOTE            tinyint(1)           not null,
    REV_TEXT            varchar(200)         null,
    REV_DATETIME_LEAVE  datetime             not null,
    REV_DATETIME_DELETE datetime             null,
    REV_VALID           tinyint(1) default 1 not null
)
    charset = utf8mb3;

create table if not exists SETTINGS
(
    SET_REF               varchar(25) not null
        primary key,
    SET_NBRMINPARTICIPANT tinyint     not null,
    SET_NBRMAXPARTICIPANT tinyint     not null,
    SET_AGE_MIN           tinyint     not null,
    SET_PWD_MIN_LENGTH    tinyint     not null,
    SET_DEFAULT_DISTANCE  tinyint     null
)
    charset = latin1;

create table if not exists SIGNALJUDGE
(
    SIGN_JUD_ID       mediumint auto_increment
        primary key,
    COM_JUD_ID        mediumint    not null,
    SIGN_JUD_DATETIME datetime     not null,
    SIGN_JUD_TEXT     varchar(160) not null,
    constraint SIGNALJUDGE_ibfk_1
        foreign key (COM_JUD_ID) references COMJUDGE (COM_JUD_ID)
)
    charset = latin1;

create index I_FK_SIGNALJUDGE_COMJUDGE
    on SIGNALJUDGE (COM_JUD_ID);

create table if not exists TASK
(
    TASK_ID                int auto_increment
        primary key,
    EVENT_ID               int        not null,
    TASK_DATETIME_DELETE   datetime   null,
    TASK_DATETIME_CREATE   datetime   not null,
    TASK_LABEL             text       not null,
    TASK_VISIBILITY        int        not null,
    TASK_PRICE_AFFECT      int        not null,
    TASK_PRICE_ONPLACE     tinyint(1) not null,
    TASK_USER_DESIGNATION  int        null,
    TASK_DATETIME_DEADLINE datetime   null,
    TASK_PRICE             double     null,
    TASK_NOTE              longtext   null,
    TASK_CATEGORY_ID       int        null
);

create table if not exists TASK_CATEGORY
(
    TASK_CATEGORY_ID    int auto_increment
        primary key,
    TASK_CATEGORY_LABEL text null
);

create table if not exists TASK_DONE
(
    TASK_ID              int      not null,
    TASK_DATETIME_UNDONE datetime null,
    USER_ID              int      not null,
    TASK_DATETIME_DONE   datetime null
);

create table if not exists USER
(
    USER_ID                    mediumint auto_increment
        primary key,
    USER_DATETIME_REGISTRATION datetime                not null,
    USER_PROFILE_PICTURE       varchar(200)            null,
    USER_DATE_BIRTH            date                    not null,
    USER_LOCATION_LABEL        varchar(100) default '' not null,
    USER_LOCATION_ADDRESS      varchar(100)            null,
    USER_LOCATION_CP           char(5)                 null,
    USER_LOCATION_CITY         varchar(100)            null,
    USER_LOCATION_COUNTRY      varchar(100)            null,
    USER_LOCATION_PLACE_ID     varchar(50)  default '' not null,
    USER_LOCATION_LNG          double                  not null,
    USER_LOCATION_LAT          double                  not null,
    USER_PASSWORD              char(32)                not null,
    USER_EMAIL                 varchar(100)            not null,
    USER_VALIDATION            char(32)                null,
    USER_DATETIME_DELETE       datetime                null,
    USER_VALID                 tinyint(1)   default 1  not null,
    USER_TYPE                  tinyint(1)              not null
)
    charset = latin1;

create table if not exists EVENT
(
    EVENT_ID                   mediumint auto_increment
        primary key,
    USER_ID                    mediumint               not null,
    CAT_ID                     tinyint                 not null,
    EVENT_TITLE                varchar(100)            not null,
    EVENT_DESCRIPTION          text                    null,
    EVENT_LOCATION_LABEL       varchar(100) default '' not null,
    EVENT_LOCATION_COMPLEMENTS varchar(100)            null,
    EVENT_LOCATION_ADDRESS     varchar(100)            null,
    EVENT_LOCATION_CP          char(5)                 null,
    EVENT_LOCATION_CITY        varchar(100)            null,
    EVENT_LOCATION_COUNTRY     varchar(100)            null,
    EVENT_LOCATION_PLACE_ID    varchar(50)  default '' not null,
    EVENT_LOCATION_LNG         double                  not null,
    EVENT_LOCATION_LAT         double                  not null,
    EVENT_DATETIME_BEGIN       datetime                not null,
    EVENT_DATETIME_END         datetime                null,
    EVENT_CIRCLE               tinyint(1)   default 2  not null,
    EVENT_PARTICIPANTS_NUMBER  tinyint                 null,
    EVENT_GUEST_ONLY           tinyint(1)   default 1  null,
    EVENT_DATETIME_CREATE      datetime                not null,
    EVENT_DATETIME_CANCEL      datetime                null,
    EVENT_DATETIME_DELETE      datetime                null,
    EVENT_VALID                tinyint      default 1  not null,
    EVENT_PRICE                float                   null,
    constraint EVENT_ibfk_1
        foreign key (USER_ID) references USER (USER_ID),
    constraint EVENT_ibfk_2
        foreign key (CAT_ID) references CATEGORY (CAT_ID)
)
    charset = latin1;

create table if not exists COMENTEVENT
(
    COM_EVENT_ID              mediumint auto_increment
        primary key,
    EVENT_ID                  mediumint            not null,
    USER_ID                   mediumint            not null,
    COM_EVENT_DATETIME_POST   datetime             not null,
    COM_EVENT_TEXT            varchar(160)         not null,
    COM_EVENT_DATETIME_DELETE datetime             null,
    COM_EVENT_VALID           tinyint(1) default 1 not null,
    constraint COMENTEVENT_ibfk_1
        foreign key (EVENT_ID) references EVENT (EVENT_ID),
    constraint COMENTEVENT_ibfk_2
        foreign key (USER_ID) references USER (USER_ID)
)
    charset = latin1;

create index I_FK_COMENTEVENT_EVENT
    on COMENTEVENT (EVENT_ID);

create index I_FK_COMENTEVENT_USER
    on COMENTEVENT (USER_ID);

create table if not exists DEFINE
(
    USER_ID    mediumint not null,
    USER_ID_1  mediumint not null,
    EVENT_ID   mediumint not null,
    COM_JUD_ID mediumint not null,
    DEF_NOTE   tinyint   not null,
    primary key (USER_ID, USER_ID_1, EVENT_ID),
    constraint DEFINE_ibfk_1
        foreign key (USER_ID) references USER (USER_ID),
    constraint DEFINE_ibfk_2
        foreign key (USER_ID_1) references USER (USER_ID),
    constraint DEFINE_ibfk_3
        foreign key (EVENT_ID) references EVENT (EVENT_ID),
    constraint DEFINE_ibfk_4
        foreign key (COM_JUD_ID) references COMJUDGE (COM_JUD_ID)
)
    charset = latin1;

create index I_FK_DEFINE_COMJUDGE
    on DEFINE (COM_JUD_ID);

create index I_FK_DEFINE_EVENT
    on DEFINE (EVENT_ID);

create index I_FK_DEFINE_USER
    on DEFINE (USER_ID);

create index I_FK_DEFINE_USER1
    on DEFINE (USER_ID_1);

create index I_FK_EVENT_CATEGORY
    on EVENT (CAT_ID);

create index I_FK_EVENT_USER
    on EVENT (USER_ID);

create table if not exists FRIENDS
(
    USER_ID             mediumint not null,
    USER_ID_1           mediumint not null,
    FRI_DATETIME_DEMAND datetime  not null,
    FRI_DATETIME_ACCEPT datetime  null,
    FRI_DATETIME_DELETE datetime  null,
    primary key (USER_ID, USER_ID_1, FRI_DATETIME_DEMAND),
    constraint FRIENDS_ibfk_1
        foreign key (USER_ID) references USER (USER_ID),
    constraint FRIENDS_ibfk_2
        foreign key (USER_ID_1) references USER (USER_ID)
)
    charset = latin1;

create index I_FK_FRIENDS_USER
    on FRIENDS (USER_ID);

create index I_FK_FRIENDS_USER1
    on FRIENDS (USER_ID_1);

create table if not exists META_USER_CLI
(
    USER_ID          mediumint              not null
        primary key,
    CLI_LASTNAME     varchar(50) default '' not null,
    CLI_FIRSTNAME    varchar(50) default '' not null,
    CLI_DESCRIPTION  text                   null,
    CLI_SEX          varchar(1)  default '' not null,
    CLI_RELATIONSHIP varchar(1)             null,
    constraint FOREIGN_CLI_USER_ID
        foreign key (USER_ID) references USER (USER_ID)
)
    charset = utf8mb3;

create table if not exists META_USER_PRO
(
    USER_ID         mediumint              not null
        primary key,
    PRO_NAME        varchar(50) default '' not null,
    PRO_DESCRIPTION text                   null,
    constraint FOREIGN_PRO_USER_ID
        foreign key (USER_ID) references USER (USER_ID)
)
    charset = utf8mb3;

create table if not exists ORGANIZE
(
    USER_ID            mediumint not null,
    EVENT_ID           mediumint not null,
    ORG_DATETIME       datetime  not null,
    ORG_VALIDE         tinyint   not null,
    ORG_DATETIME_UNSET datetime  null,
    ORG_DATETIME_SET   datetime  null,
    primary key (USER_ID, EVENT_ID),
    constraint ORGANIZE_ibfk_1
        foreign key (USER_ID) references USER (USER_ID),
    constraint ORGANIZE_ibfk_2
        foreign key (EVENT_ID) references EVENT (EVENT_ID)
)
    charset = latin1;

create index I_FK_ORGANIZE_EVENT
    on ORGANIZE (EVENT_ID);

create index I_FK_ORGANIZE_USER
    on ORGANIZE (USER_ID);

create table if not exists QUALIFIES
(
    USER_ID   mediumint not null,
    EVENT_ID  mediumint not null,
    USER_ID_1 mediumint not null,
    ADJ_ID    tinyint   not null,
    primary key (USER_ID, EVENT_ID, USER_ID_1, ADJ_ID),
    constraint QUALIFIES_ibfk_1
        foreign key (USER_ID) references USER (USER_ID),
    constraint QUALIFIES_ibfk_2
        foreign key (EVENT_ID) references EVENT (EVENT_ID),
    constraint QUALIFIES_ibfk_3
        foreign key (USER_ID_1) references USER (USER_ID),
    constraint QUALIFIES_ibfk_4
        foreign key (ADJ_ID) references ADJECTIVE (ADJ_ID)
)
    charset = latin1;

create index I_FK_QUALIFIES_ADJECTIVE
    on QUALIFIES (ADJ_ID);

create index I_FK_QUALIFIES_EVENT
    on QUALIFIES (EVENT_ID);

create index I_FK_QUALIFIES_USER
    on QUALIFIES (USER_ID);

create index I_FK_QUALIFIES_USER1
    on QUALIFIES (USER_ID_1);

create table if not exists SIGNALCOM
(
    SIGN_COM_ID       mediumint auto_increment
        primary key,
    COM_EVENT_ID      mediumint    not null,
    SIGN_COM_DATETIME datetime     not null,
    SIGN_COM_TEXT     varchar(160) not null,
    constraint SIGNALCOM_ibfk_1
        foreign key (COM_EVENT_ID) references COMENTEVENT (COM_EVENT_ID)
)
    charset = latin1;

create index I_FK_SIGNALCOM_COMENTEVENT
    on SIGNALCOM (COM_EVENT_ID);

create table if not exists SIGNALUSER
(
    SIGN_USER_ID       mediumint auto_increment
        primary key,
    USER_ID            mediumint not null,
    SIGN_USER_DATETIME datetime  not null,
    SIGN_USER_TEXT     char(160) null,
    constraint SIGNALUSER_ibfk_1
        foreign key (USER_ID) references USER (USER_ID)
)
    charset = latin1;

create index I_FK_SIGNALUSER_USER
    on SIGNALUSER (USER_ID);

