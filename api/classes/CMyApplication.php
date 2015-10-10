<?php
/*
** OnceMon
** Copyright (C) 2014-2015 ISCAS
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * Class containing methods for applications
 *
 * @package API
 */
class CMyApplication extends CZBXAPI
{

    protected $tableName = 't_custom_myapplication';
    protected $tableAlias = 'myapp';
    protected $sortColumns = array('applicationid');


    public function create($myapplications)
    {
        $myapplications = zbx_toArray($myapplications);
//		foreach ($myapplications as $mediatype) {
//			$mediatpeExist = $this->get(array(
//				'filter' => array('description' => $mediatype['description']),
//				'output' => API_OUTPUT_EXTEND
//			));
//			if (!empty($mediatypeExist)) {
//				self::e//xception(ZBX_API_ERROR_PARAMETERS, _s('Media type "%s" already exists.', $mediatypeExist[0]['description']));
//			}
//		}

        $myapplicationids = DB::insert('t_custom_myapplication', $myapplications);

        return array('myapplicationids' => $myapplicationids);
    }


    public function get($options = array())
    {
        $result = array();

        $sqlParts = array(
            'select' => array('t_custom_myapplication' => 'myapp.applicationid'),
            'from' => array('t_custom_myapplication' => 't_custom_myapplication myapp'),
            'where' => array(),
            'group' => array(),
            'order' => array(),
            'limit' => null
        );

        $defOptions = array(
            'applicationids' => null,
            'editable' => null,
            // filter
            'filter' => null,
            'search' => null,
            'searchByAny' => null,
            'startSearch' => null,
            'excludeSearch' => null,
            'searchWildcardsEnabled' => null,
            // output
            'output' => API_OUTPUT_REFER,
            'selectUsers' => null,
            'countOutput' => null,
            'groupCount' => null,
            'preservekeys' => null,
            'sortfield' => '',
            'sortorder' => '',
            'limit' => null
        );
        $options = zbx_array_merge($defOptions, $options);

        // mediatypeids
        if (!is_null($options['applicationids'])) {
            zbx_value2array($options['applicationids']);
            $sqlParts['where'][] = dbConditionInt('myapp.applicationid', $options['applicationids']);
        }

        // filter
        if (is_array($options['filter'])) {
            $this->dbFilter('t_custom_myapplication myapp', $options, $sqlParts);
        }

        // search
        if (is_array($options['search'])) {
            zbx_db_search('t_custom_myapplication myapp', $options, $sqlParts);
        }

        // limit
        if (zbx_ctype_digit($options['limit']) && $options['limit']) {
            $sqlParts['limit'] = $options['limit'];
        }

        $sqlParts = $this->applyQueryOutputOptions($this->tableName(), $this->tableAlias(), $options, $sqlParts);
        $sqlParts = $this->applyQuerySortOptions($this->tableName(), $this->tableAlias(), $options, $sqlParts);
        $sqlParts = $this->applyQueryNodeOptions($this->tableName(), $this->tableAlias(), $options, $sqlParts);
        $res = DBselect($this->createSelectQueryFromParts($sqlParts), $sqlParts['limit']);
        while ($myapplication = DBfetch($res)) {
            if (!is_null($options['countOutput'])) {
                if (!is_null($options['groupCount'])) {
                    $result[] = $myapplication;
                } else {
                    $result = $myapplication['rowscount'];
                }
            } else {
                if (!isset($result[$myapplication['applicationid']])) {
                    $result[$myapplication['applicationid']] = array();
                }
                $result[$myapplication['applicationid']] += $myapplication;
            }
        }

        if (!is_null($options['countOutput'])) {
            return $result;
        }

        // removing keys (hash -> array)
        if (is_null($options['preservekeys'])) {
            $result = zbx_cleanHashes($result);
        }
        return $result;
    }


    public function update($mediatypes)
    {
        if (USER_TYPE_SUPER_ADMIN != self::$userData['type']) {
            self::exception(ZBX_API_ERROR_PERMISSIONS, _('Only Super Admins can edit media types.'));
        }

        $mediatypes = zbx_toArray($mediatypes);

        $update = array();
        foreach ($mediatypes as $mediatype) {
            $mediatypeDbFields = array(
                'mediatypeid' => null
            );
            if (!check_db_fields($mediatypeDbFields, $mediatype)) {
                self::exception(ZBX_API_ERROR_PARAMETERS, _('Wrong fields for media type.'));
            }

            if (isset($mediatype['description'])) {
                $options = array(
                    'filter' => array('description' => $mediatype['description']),
                    'preservekeys' => true,
                    'output' => array('mediatypeid')
                );
                $existMediatypes = $this->get($options);
                $existMediatype = reset($existMediatypes);

                if ($existMediatype && bccomp($existMediatype['mediatypeid'], $mediatype['mediatypeid']) != 0) {
                    self::exception(ZBX_API_ERROR_PARAMETERS, _s('Media type "%s" already exists.', $mediatype['description']));
                }
            }

            if (array_key_exists('passwd', $mediatype) && empty($mediatype['passwd'])) {
                self::exception(ZBX_API_ERROR_PARAMETERS, _('Password required for media type.'));
            }

            if (array_key_exists('type', $mediatype) && !in_array($mediatype['type'], array(MEDIA_TYPE_JABBER, MEDIA_TYPE_EZ_TEXTING))) {
                $mediatype['passwd'] = '';
            }

            $mediatypeid = $mediatype['mediatypeid'];
            unset($mediatype['mediatypeid']);

            if (!empty($mediatype)) {
                $update[] = array(
                    'values' => $mediatype,
                    'where' => array('mediatypeid' => $mediatypeid)
                );
            }
        }

        DB::update('media_type', $update);
        $mediatypeids = zbx_objectValues($mediatypes, 'mediatypeid');

        return array('mediatypeids' => $mediatypeids);
    }

    public function delete($mediatypeids)
    {
        if (self::$userData['type'] != USER_TYPE_SUPER_ADMIN) {
            self::exception(ZBX_API_ERROR_PERMISSIONS, _('Only Super Admins can delete media types.'));
        }

        $mediatypeids = zbx_toArray($mediatypeids);

        $actions = API::Action()->get(array(
            'mediatypeids' => $mediatypeids,
            'output' => API_OUTPUT_EXTEND,
            'preservekeys' => true
        ));
        if (!empty($actions)) {
            $action = reset($actions);
            self::exception(ZBX_API_ERROR_PARAMETERS, _s('Media types used by action "%s".', $action['name']));
        }

        DB::delete('media_type', array('mediatypeid' => $mediatypeids));

        return array('mediatypeids' => $mediatypeids);
    }
}
