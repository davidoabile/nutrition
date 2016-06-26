
/**
 * One Step Checkout Manager : One Step Checkout Manager (CFM Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckoutfields
 * @version      1.0.9 - 2.9.8
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
function parse_version(version)
{
    version = version.replace(/\./g, '');
    if (5 > version.length)
    {
        version += str_repeat('0', 4 - version.length);
        
    }
    return parseInt(version) || 0;
}

function str_repeat(input, multiplier)
{
    var buf = '';
    for (i=0; i < multiplier; i++)
    {
        buf += input;
    }
    return buf;
}

function version_compare(ver1, ver2)
{
    ver1 = parse_version(ver1), ver2 = parse_version(ver2);
    
    if (ver1 == ver2)
    {
        return 0;
    }
    
    if (ver1 > ver2)
    {
        return 1;
    }
    else
    {
        return -1;
    }
}

if (Prototype && Prototype.Version)
{
    if (0 <= version_compare(Prototype.Version, '1.7.0.0'))
    {
        if (Form && Form.serializeElements)
        {
            // This is Form.serialize implementation from Prototype 1.6.0.3. In 1.7 they changed how elements are proccessed.
            Form.serializeElements = function(elements, options)
            {
                if (typeof options != 'object')
                {
                    options = { hash: !!options };
                }
                else
                {
                    if (Object.isUndefined(options.hash))
                    {
                        options.hash = true;
                    }
                }
                var key, value, submitted = false, submit = options.submit;

                var data = elements.inject({ }, function(result, element) {
                    if (!element.disabled && element.name)
                    {
                        key = element.name; value = $(element).getValue();
                        if (value != null && element.type != 'file' && (element.type != 'submit' || (!submitted && submit !== false && (!submit || key == submit) && (submitted = true))))
                        {
                            if (key in result)
                            {
                                if (!Object.isArray(result[key]))
                                {
                                    result[key] = [result[key]];
                                }
                                result[key].push(value);
                            }
                            else
                            {
                                result[key] = value;
                            }
                        }
                    }
                    return result;
                });

                return options.hash ? data : Object.toQueryString(data);
            };
        }
    }
}