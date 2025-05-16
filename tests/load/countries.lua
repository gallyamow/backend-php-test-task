wrk.method = "POST"
wrk.headers["Content-Type"] = "application/json"

local countries = { "US", "CA", "GB", "DE", "FR", "RU", "JP", "IN", "CN", "BR", "AU", "IT", "ES", "NL", "PL", "SE", "CH", "TR", "NZ", "MX", "SG", "KR", "TH", "IL", "PT", "IE", "NO", "BE", "DK", "FI", "ZA", "AE", "SA", "GR", "HK", "CZ", "RO", "HU", "VN", "PH", "MY", "ID", "QA", "KZ", "BY", "AR", "CL", "CO", "PE", "EG", "NG", "PK", "BD", "UA", "RS", "KE", "PE", "GH", "TZ", "UG", "ZW", "LR", "SD", "ET", "SO", "DZ", "MA", "TN", "LY", "GM", "SN", "ML", "BF", "NE", "TG", "BJ", "MR", "LR", "SL", "GW", "CV", "KM", "ST", "GQ", "GA", "CG", "CD", "AO", "GW", "IO", "SC", "SD", "RW", "ET", "SO", "DJ", "KE", "TZ", "UG", "BI", "MZ", "ZM", "MG", "RE", "ZW", "NA", "MW", "LS", "BW", "SZ", "KM", "SH", "ER", "AW", "FO", "GL", "GI", "PT", "LU", "IE", "IS", "AL", "MT", "CY", "FI", "BG", "LT", "LV", "EE", "MD", "AM", "BY", "AD", "MC", "SM", "VA", "UA", "RS", "ME", "HR", "SI", "BA", "MK", "IT", "RO", "BG", "TR", "GE", "AZ", "TM", "KG", "UZ", "TJ", "BT", "IN", "PK", "BD", "LK", "NP", "MM", "TH", "LA", "KH", "VN", "MY", "BN", "SG", "ID", "TL", "PH", "TW", "CN", "JP", "KR", "KP"}

function request()
    local countryCode = countries[math.random(#countries)]
    wrk.body = '{"countryCode": "' .. countryCode .. '"}'
    return wrk.format(nil, "/v1/statistics")
end