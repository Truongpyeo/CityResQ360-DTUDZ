/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

"use strict";function run(){var e,r,t,a=window.opener.swaggerUIRedirectOauth2,o=a.state,n=a.redirectUrl;if((t=(r=/code|token|error/.test(window.location.hash)?window.location.hash.substring(1).replace("?","&"):location.search.substring(1)).split("&")).forEach((function(e,r,t){t[r]='"'+e.replace("=",'":"')+'"'})),e=(r=r?JSON.parse("{"+t.join()+"}",(function(e,r){return""===e?r:decodeURIComponent(r)})):{}).state===o,"accessCode"!==a.auth.schema.get("flow")&&"authorizationCode"!==a.auth.schema.get("flow")&&"authorization_code"!==a.auth.schema.get("flow")||a.auth.code)a.callback({auth:a.auth,token:r,isValid:e,redirectUrl:n});else if(e||a.errCb({authId:a.auth.name,source:"auth",level:"warning",message:"Authorization may be unsafe, passed state was changed in server Passed state wasn't returned from auth server"}),r.code)delete a.state,a.auth.code=r.code,a.callback({auth:a.auth,redirectUrl:n});else{let e;r.error&&(e="["+r.error+"]: "+(r.error_description?r.error_description+". ":"no accessCode received from the server. ")+(r.error_uri?"More info: "+r.error_uri:"")),a.errCb({authId:a.auth.name,source:"auth",level:"error",message:e||"[Authorization failed]: no accessCode received from the server"})}window.close()}"loading"!==document.readyState?run():document.addEventListener("DOMContentLoaded",(function(){run()}));