/**
 * Time Regex returns true/false
 *
 * @return {boolean}
 **/
export function timeRegex( value ) {
  const timeRegex = /^([0-9]{1,3})([:|.]([0-9]{1,3})([:|.]([0-9]{1,3}))?)?$/gm;
  return timeRegex.test(value);
}