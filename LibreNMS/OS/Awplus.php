<?php

/**
 * Awplus.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Awplus extends OS implements OSDiscovery, TransceiverDiscovery
{
    public function discoverOS(Device $device): void
    {
        //$hardware and $serial use snmp_getnext as the OID for these is not always fixed.
        //However, the first OID is the device baseboard.

        $response = SnmpQuery::next(['AT-RESOURCE-MIB::rscBoardName', 'AT-RESOURCE-MIB::rscBoardSerialNumber']);
        $hardware = $response->value('AT-RESOURCE-MIB::rscBoardName');
        $serial = $response->value('AT-RESOURCE-MIB::rscBoardSerialNumber');

        // SBx8100 platform has line cards show up first in "rscBoardName" above.
        //Instead use sysObjectID.0

        if (Str::contains($hardware, 'SBx81')) {
            $data_array = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'rscBoardName', [], 'AT-RESOURCE-MIB', '-OUsb');
            $data_array = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'rscBoardSerialNumber', $data_array, 'AT-RESOURCE-MIB', '-OUsb');

            $hardware = snmp_translate($device->sysObjectID, 'AT-PRODUCT-MIB', null, null, $this->getDeviceArray());
            $hardware = str_replace('at', 'AT-', $hardware);

            // Features and Serial is set to Controller card 1.5
            $features = $data_array['5.6']['rscBoardName'];
            $serial = $data_array['5.6']['rscBoardSerialNumber'];

            // If bay 1.5 is empty, set to Controller card 1.6
            if (! $features && ! $serial) {
                $features = $data_array['6.6']['rscBoardName'];
                $serial = $data_array['6.6']['rscBoardSerialNumber'];
            }
        }

        $device->version = snmp_get($this->getDeviceArray(), 'currSoftVersion.0', '-OQv', 'AT-SETUP-MIB');
        $device->serial = $serial;
        $device->hardware = $hardware;
        $device->features = $features ?? null;
    }

    public function discoverTransceivers(): Collection
    {
        return \SnmpQuery::enumStrings()->walk('AT-SYSINFO-MIB::atPortInfoTransceiverTable')
            ->mapTable(function ($data, $ifIndex) {
                return new Transceiver([
                    'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                    'index' => $ifIndex,
                    'type' => $data['AT-SYSINFO-MIB::atPortInfoTransceiverType'] ?? null,
                    'entity_physical_index' => $ifIndex,
                ]);
            });
    }
}
